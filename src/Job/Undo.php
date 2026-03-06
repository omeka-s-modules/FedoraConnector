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
        if ($fedoraItems) {
            foreach ($fedoraItems as $fedoraItem) {
                $fedoraResponse = $api->delete('fedora_items', $fedoraItem->id());
                $itemResponse = $api->delete('items', $fedoraItem->item()->id());
                $deletedItemCount++;
            }
        }

        if ($deletedItemCount) {
            $deletedItemComment = $deletedItemCount . ' items deleted';
            $comment = strlen($comment) ? $comment . '; ' . $deletedItemComment : $deletedItemComment;
        }
        $fedoraImportJson = [
                            'o:job' => ['o:id' => $this->job->getId()],
                            'comment' => $comment,
                            'added_count' => 0,
                            'updated_count' => 0,
                          ];
        $response = $api->create('fedora_imports', $fedoraImportJson);
        $jobArgs = $this->job->getArgs();
        $jobArgs['comment'] = $comment;
        $this->job->setArgs($jobArgs);
    }
}
