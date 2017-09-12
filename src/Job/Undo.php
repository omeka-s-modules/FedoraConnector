<?php
namespace FedoraConnector\Job;

use Omeka\Job\AbstractJob;

class Undo extends AbstractJob
{
    public function perform()
    {
        $jobId = $this->getArg('jobId');
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $response = $api->search('fedora_items', ['job_id' => $jobId]);
        $fedoraItems = $response->getContent();
        if ($fedoraItems) {
            foreach ($fedoraItems as $fedoraItem) {
                $fedoraResponse = $api->delete('fedora_items', $fedoraItem->id());
                $itemResponse = $api->delete('items', $fedoraItem->item()->id());
            }
        }
    }
}
