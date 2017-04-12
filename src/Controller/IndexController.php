<?php
namespace FedoraConnector\Controller;

use FedoraConnector\Form\ImportForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel;
        $form = $this->getForm(ImportForm::class);
        $view->setVariable('form', $form);
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $uri = $data['container_uri'];
                // do a quick check that the endpoint is available
                if (! file_get_contents($uri)) {
                    $this->messenger()->addError('There was a problem connecting to the Fedora Container URI');
                    return $view;
                }

                $job = $this->jobDispatcher()->dispatch('FedoraConnector\Job\Import', $data);
                //the FedoraImport record is created in the job, so it doesn't
                //happen until the job is done
                $this->messenger()->addSuccess('Importing in Job ID ' . $job->getId());
                $view->setVariable('job', $job);
                return $this->redirect()->toRoute('admin/fedora-connector/past-imports');
            } else {
                $this->messenger()->addError('There was an error during validation');
            }
        }

        
        return $view;
    }

    public function pastImportsAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $undoJobIds = [];
            foreach ($data['jobs'] as $jobId) {
                $undoJob = $this->undoJob($jobId);
                $undoJobIds[] = $undoJob->getId();
            }
            $this->messenger()->addSuccess('Undo in progress in the following jobs: ' . implode(', ', $undoJobIds));
        }
        $view = new ViewModel;
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->params()->fromQuery() + array(
            'page'       => $page,
            'sort_by'    => $this->params()->fromQuery('sort_by', 'id'),
            'sort_order' => $this->params()->fromQuery('sort_order', 'desc')
        );
        $response = $this->api()->search('fedora_imports', $query);
        $this->paginator($response->getTotalResults(), $page);
        $view->setVariable('imports', $response->getContent());
        return $view;
    }

    protected function undoJob($jobId) {
        $response = $this->api()->search('fedora_imports', array('job_id' => $jobId));
        $fedoraImport = $response->getContent()[0];
        $job = $this->jobDispatcher()->dispatch('FedoraConnector\Job\Undo', array('jobId' => $jobId));
        $response = $this->api()->update('fedora_imports', 
                    $fedoraImport->id(), 
                    array(
                        'o:undo_job' => array('o:id' => $job->getId() )
                    )
                );
        return $job;
    }
}
