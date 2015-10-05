<?php 
namespace FedoraConnector\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FedoraItemRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return array(
            'last_modified' => $this->getData()->getLastModified(),
            'uri'           => $this->getData()->getUri(),
            'o:item'        => $this->getReference(
                null,
                $this->getData()->getItem(),
                $this->getAdapter('items')
            ),
            'o:job'         => $this->getReference(
                null,
                $this->getData()->getJob(),
                $this->getAdapter('jobs')
            ),
        );
    }
    
    public function getJsonLdType()
    {
        return 'o:FedoraItem';
    }

    public function lastModified()
    {
        return $this->getData()->getlastModified();
    }
    
    public function uri()
    {
        return $this->getData()->getUri();
    }

    public function item()
    {
        return $this->getAdapter('items')
            ->getRepresentation(null, $this->getData()->getItem());
    }
    
    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation(null, $this->getData()->getJob());
    }
}
