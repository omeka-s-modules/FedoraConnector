<?php
namespace FedoraConnector\Controller;

use FedoraConnector\Form\ImportForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Stdlib\Message;

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
                if (! @file_get_contents($uri)) {
                    $this->messenger()->addError('There was a problem connecting to the Fedora Container URI'); // @translate
                    return $view;
                }

                $job = $this->jobDispatcher()->dispatch('FedoraConnector\Job\Import', $data);
                //the FedoraImport record is created in the job, so it doesn't
                //happen until the job is done
                $message = new Message('Importing in Job ID %s', // @translate
                                        $job->getId());
                $this->messenger()->addSuccess($message);
                $view->setVariable('job', $job);
                return $this->redirect()->toRoute('admin/fedora-connector/past-imports');
            } else {
                $this->messenger()->addError('There was an error during validation'); // @translate
            }
        }

        return $view;
    }

    public function pastImportsAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['undoJobs'])) {
                $undoJobIds = [];
                foreach ($data['undoJobs'] as $jobId) {
                    $undoJob = $this->undoJob($jobId);
                    $undoJobIds[] = $jobId;
                }
                $message = new Message('Undo in progress on the following jobs: %s', // @translate
                    implode(', ', $undoJobIds));
                $this->messenger()->addSuccess($message);
            }
            if (isset($data['rerunJobs'])) {
                $rerunJobIds = [];
                foreach ($data['rerunJobs'] as $jobId) {
                    $rerunJob = $this->rerunJob($jobId);
                    $rerunJobIds[] = $jobId;
                }
                $message = new Message('Rerun in progress on the following jobs: %s', // @translate
                    implode(', ', $rerunJobIds));
                $this->messenger()->addSuccess($message);
            }
            if (!isset($data['undoJobs']) && !isset($data['rerunJobs'])){
                $this->messenger()->addError('Error: no jobs selected'); // @translate
            }
        }
        $view = new ViewModel;
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->params()->fromQuery() + [
            'page' => $page,
            'sort_by' => $this->params()->fromQuery('sort_by', 'id'),
            'sort_order' => $this->params()->fromQuery('sort_order', 'desc'),
        ];
        $response = $this->api()->search('fedora_imports', $query);
        $this->paginator($response->getTotalResults(), $page);
        $view->setVariable('imports', $response->getContent());
        return $view;
    }

    protected function undoJob($jobId)
    {
        $response = $this->api()->search('fedora_imports', ['job_id' => $jobId]);
        $fedoraImport = $response->getContent()[0];
        $job = $this->jobDispatcher()->dispatch('FedoraConnector\Job\Undo', ['jobId' => $jobId]);
        $response = $this->api()->update('fedora_imports',
                    $fedoraImport->id(),
                    [
                        'o:undo_job' => ['o:id' => $job->getId() ],
                    ]
                );
        return $job;
    }

    protected function rerunJob($jobId)
    {
        $response = $this->api()->search('fedora_imports', ['job_id' => $jobId]);
        $fedoraImport = $response->getContent()[0];
        // Get original import job args to run again
        $rerunData = $fedoraImport->job()->args();
        $job = $this->jobDispatcher()->dispatch('FedoraConnector\Job\Import', $rerunData);
        $response = $this->api()->update('fedora_imports',
                $fedoraImport->id(),
                [
                    'o:rerun_job' => ['o:id' => $job->getId() ],
                ]
            );
        return $job;
    }
}
