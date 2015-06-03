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
        $form = new ImportForm($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $dispatcher = $this->getServiceLocator()->get('Omeka\JobDispatcher');
                $job = $dispatcher->dispatch('FedoraConnector\Job\Import', $data);
                //the FedoraImport record is created in the job, so it doesn't
                //happen until the job is done
                $view->setVariable('job', $job);
            } else {
                $this->messenger()->addError('There was an error during validation');
            }
        }

        $view->setVariable('form', $form);
        return $view;
    }

    public function pastImportsAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            foreach ($data['jobs'] as $jobId) {
                $this->undoJob($jobId);
            }
        }
        $view = new ViewModel;
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->params()->fromQuery() + array('page' => $page);
        $response = $this->api()->search('fedora_imports');
        $this->paginator($response->getTotalResults(), $page);
        $view->setVariable('imports', $response->getContent());
        return $view;
    }

    protected function undoJob($jobId) {
        $response = $this->api()->search('fedora_imports', array('job_id' => $jobId));
        if ($response->isError()) {

        }
        $fedoraImport = $response->getContent()[0];
        $dispatcher = $this->getServiceLocator()->get('Omeka\JobDispatcher');
        $job = $dispatcher->dispatch('FedoraConnector\Job\Undo', array('jobId' => $jobId));
        $response = $this->api()->update('fedora_imports', 
                    $fedoraImport->id(), 
                    array(
                        'o:undo_job' => array('o:id' => $job->getId() )
                    )
                );
        if ($response->isError()) {
            echo 'fail';
            die();
        }
    }
}