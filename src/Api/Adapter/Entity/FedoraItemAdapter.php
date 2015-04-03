<?php 
namespace FedoraConnector\Api\Adapter\Entity;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\Entity\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Model\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class FedoraItemAdapter extends AbstractEntityAdapter
{
    public function getEntityClass()
    {
        return 'FedoraConnector\Model\Entity\FedoraItem';
    }
    
    public function getResourceName()
    {
        return 'fedora_items';
    }
    
    public function getRepresentationClass()
    {
        return 'FedoraConnector\Api\Representation\Entity\FedoraItemRepresentation';
    }
    
    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['uri'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.uri',
                $this->createNamedParameter($qb, $query['uri']))
            );
        }
        
        if (isset($query['api_url'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.apiUrl',
                $this->createNamedParameter($qb, $query['api_url']))
            );
        }
        
    }
    
    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        $data = $request->getContent();
        if (isset($data['o:job']['o:id'])) {
            $job = $this->getAdapter('jobs')->findEntity($data['o:job']['o:id']);
            $entity->setJob($job);
        }
        if (isset($data['api_url'])) {
            $entity->setApiUrl($data['api_url']);
        }
        if (isset($data['o:item']['o:id'])) {
            $item = $this->getAdapter('items')->findEntity($data['o:item']['o:id']);
            $entity->setItem($item);
        }
        if (isset($data['uri'])) {
            $entity->setRemoteId($data['uri']);
        }

        if (isset($data['last_modified'])) {
            $entity->setLastModified($data['last_modified']);
        }
    }
}