<?php
namespace FedoraConnector\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FedoraImportRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return array(
            'added_count' => $this->resource->getAddedCount(),
            'updated_count' => $this->resource->getUpdatedCount(),
            'comment'        => $this->resource->getComment(),
            'o:job'          => $this->getReference(
                null,
                $this->resource->getJob(),
                $this->getAdapter('jobs')
            ),
            'o:undo_job'     => $this->getReference(
                null,
                $this->resource->getUndoJob(),
                $this->getAdapter('jobs')
            ),
        );
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
