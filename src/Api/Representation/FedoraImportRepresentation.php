<?php
namespace FedoraConnector\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FedoraImportRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return array(
            'resource_count' => $this->getData()->getResourceCount,
            'comment'        => $this->getData()->getComment(),
            'o:job'          => $this->getReference(
                null,
                $this->getData()->getJob(),
                $this->getAdapter('jobs')
            ),
            'o:undo_job'     => $this->getReference(
                null,
                $this->getData()->getUndoJob(),
                $this->getAdapter('jobs')
            ),
        );
    }
    
    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation(null, $this->getData()->getJob());
    }
    
    public function undoJob()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation(null, $this->getData()->getUndoJob());
    }
    
    public function comment()
    {
        return $this->getData()->getComment();
    }
    
    public function resourceCount()
    {
        return $this->getData()->getResourceCount();
    }
}