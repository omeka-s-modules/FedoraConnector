<?php
namespace FedoraConnector\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class FedoraImportAdapter extends AbstractEntityAdapter
{
    public function getEntityClass()
    {
        return 'FedoraConnector\Entity\FedoraImport';
    }

    public function getResourceName()
    {
        return 'fedora_imports';
    }

    public function getRepresentationClass()
    {
        return 'FedoraConnector\Api\Representation\FedoraImportRepresentation';
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['o:job']['o:id'])) {
            $job = $this->getAdapter('jobs')->findEntity($data['o:job']['o:id']);
            $entity->setJob($job);
        }

        if (isset($data['o:undo_job']['o:id'])) {
            $job = $this->getAdapter('jobs')->findEntity($data['o:undo_job']['o:id']);
            $entity->setUndoJob($job);
        }

        if (isset($data['o:rerun_job']['o:id'])) {
            $job = $this->getAdapter('jobs')->findEntity($data['o:rerun_job']['o:id']);
            $entity->setRerunJob($job);
         }

        if (isset($data['added_count'])) {
            $entity->setAddedCount($data['added_count']);
        }

        if (isset($data['updated_count'])) {
            $entity->setUpdatedCount($data['updated_count']);
        }

        if (isset($data['comment'])) {
            $entity->setComment($data['comment']);
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['job_id'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.job',
                $this->createNamedParameter($qb, $query['job_id']))
            );
        }
    }
}
