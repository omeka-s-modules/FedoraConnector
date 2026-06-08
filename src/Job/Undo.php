<?php
namespace FedoraConnector\Job;

use Omeka\Job\AbstractJob;

class Undo extends AbstractJob
{
    public function perform()
    {
        $jobId = $this->getArg('previous_job');
        $comment = $this->getArg('comment');
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');

        // Delete items
        $response = $api->search('fedora_items', ['job_id' => $jobId]);
        $fedoraItems = $response->getContent();
        $deletedItemCount = 0;
        $deletedFileCount = 0;
        if ($fedoraItems) {
            foreach ($fedoraItems as $fedoraItem) {
                $deletedFileCount += count($fedoraItem->item()->media());
                $api->delete('fedora_items', $fedoraItem->id());
                $api->delete('items', $fedoraItem->item()->id());
                $deletedItemCount++;
            }
        }

        $commentParts = array_filter([
            $comment,
            $deletedItemCount ? $deletedItemCount . ' items deleted' : null,
            $deletedFileCount ? $deletedFileCount . ' files deleted' : null,
        ]);
        $comment = implode('; ', $commentParts);
        $fedoraImportJson = [
            'o:job' => ['o:id' => $this->job->getId()],
            'comment' => $comment,
            'added_count' => 0,
            'updated_count' => 0,
            'added_files' => 0,
        ];
        $response = $api->create('fedora_imports', $fedoraImportJson);
        $jobArgs = $this->job->getArgs();
        $jobArgs['comment'] = $comment;
        $this->job->setArgs($jobArgs);
    }
}
