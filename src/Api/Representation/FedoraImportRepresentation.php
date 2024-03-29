<?php
namespace FedoraConnector\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FedoraImportRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        if ($this->undoJob()) {
            $undo_job = $this->undoJob()->getReference();
        }
        if ($this->rerunJob()) {
            $rerun_job = $this->rerunJob()->getReference();
        }
        return [
            'added_count' => $this->resource->getAddedCount(),
            'updated_count' => $this->resource->getUpdatedCount(),
            'comment' => $this->resource->getComment(),
            'o:job' => $this->getReference(),
            'o:undo_job' => $undo_job,
            'o:rerun_job' => $rerun_job,
        ];
    }

    public function getJsonLdType()
    {
        return 'o:FedoraImport';
    }

    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    public function undoJob()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getUndoJob());
    }

    public function rerunJob()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getRerunJob());
    }

    public function comment()
    {
        return $this->resource->getComment();
    }

    public function addedCount()
    {
        return $this->resource->getAddedCount();
    }

    public function updatedCount()
    {
        return $this->resource->getUpdatedCount();
    }
}
