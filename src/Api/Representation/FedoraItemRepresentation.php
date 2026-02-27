<?php
namespace FedoraConnector\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FedoraItemRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'last_modified' => $this->lastModified(),
            'uri' => $this->uri(),
            'o:item' => $this->item(),
            'o:job' => $this->job(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:FedoraItem';
    }

    public function lastModified()
    {
        return $this->resource->getlastModified();
    }

    public function uri()
    {
        return $this->resource->getUri();
    }

    public function item()
    {
        return $this->getAdapter('items')
            ->getRepresentation($this->resource->getItem());
    }

    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }
}
