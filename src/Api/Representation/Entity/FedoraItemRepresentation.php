<?php 
namespace FedoraConnector\Api\Representation\Entity;

use Omeka\Api\Representation\Entity\AbstractEntityRepresentation;

class FedoraItemRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return array(
            'last_modified' => $this->getData()->getLastModified(),
            'api_url'       => $this->getData()->getApiUrl(),
            'remote_id'     => $this->getData()->getRemoteId(),
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

    public function lastModified()
    {
        return $this->getData()->getlastModified();
    }
    
    public function apiUrl()
    {
        return $this->getData()->getApiUrl();
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
