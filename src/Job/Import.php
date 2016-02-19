<?php
namespace FedoraConnector\Job;

use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use FedoraConnector\Entity\FedoraItem;
use FedoraConnector\Entity\FedoraImport;
use Zend\Http\Client;
use EasyRdf_Graph;
use EasyRdf_Resource;

class Import extends AbstractJob
{
    protected $client;

    protected $propertyUriIdMap;

    protected $api;
    
    protected $itemSetId;

    protected $addedCount;
    
    protected $updatedCount;
    
    public function perform()
    {
        $this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $fedoraImportJson = array(
                            'o:job'         => array('o:id' => $this->job->getId()),
                            'comment'       => 'Job started',
                            'added_count'   => 0,
                            'updated_count' => 0
                          );
        $response = $this->api->create('fedora_imports', $fedoraImportJson);
        $importRecordId = $response->getContent()->id();

        $this->addedCount = 0;
        $this->updatedCount = 0;

        $this->propertyUriIdMap = array();
        $this->client = $this->getServiceLocator()->get('Omeka\HttpClient');
        $this->client->setHeaders(array('Prefer' => 'return=representation; include="http://fedora.info/definitions/v4/repository#EmbedResources"'));
        $uri = $this->getArg('container_uri');
        $this->itemSetId = $this->getArg('itemSet', false);
        //importContainer calls itself on all child containers
        $this->importContainer($uri);
        $comment = $this->getArg('comment');
        $fedoraImportJson = array(
                            'o:job'         => array('o:id' => $this->job->getId()),
                            'comment'       => $comment,
                            'added_count'   => $this->addedCount,
                            'updated_count' => $this->updatedCount
                          );
        $response = $this->api->update('fedora_imports', $importRecordId, $fedoraImportJson);
        if ($response->isError()) {
            echo 'fail creating fedora import';
        }
    }

    public function importContainer($uri)
    {
        //see if the item has already been imported
        $response = $this->api->search('fedora_items', array('uri' => $uri));
        $content = $response->getContent();
        if (empty ($content)) {
            $fedoraItem = false;
            $omekaItem = false;
        } else {
            $fedoraItem = $content[0];
            $omekaItem = $fedoraItem->item();
        }
        
        
        $this->client->setUri($uri);
        $response = $this->client->send();
        $rdf = $response->getBody();
        $graph = new EasyRdf_Graph();
        $graph->parse($rdf);

        $containerToImport = $graph->resource($uri);
        $containers = $graph->allOfType("http://fedora.info/definitions/v4/repository#Container");
        $binaries = $graph->allOfType("http://fedora.info/definitions/v4/repository#Binary");

        $json = $this->resourceToJson($containerToImport);

        if ($this->getArg('ingest_files')) {
            foreach ($binaries as $binary) {
                $mediaJson = $this->resourceToJson($binary);
                $mediaJson['o:ingester'] = 'url';
                $mediaJson['o:source'] = $binary->getUri();
                $mediaJson['ingest_url'] = $binary->getUri();
                $json['o:media'][] = $mediaJson;
            }
        }

        if ($omekaItem) {
            $response = $this->api->update('items', $omekaItem->id(), $json);
            $itemId = $omekaItem->id();
        } else {
            $response = $this->api->create('items', $json);
            $itemId = $response->getContent()->id();
        }

        if ($response->isError()) {
            throw new Exception\RuntimeException('There was an error during item creation or update.');
        }

        $lastModifiedProperty = new EasyRdf_Resource('http://fedora.info/definitions/v4/repository#lastModified');
        $lastModifiedLiteral = $containerToImport->getLiteral($lastModifiedProperty);
        if ($lastModifiedLiteral) {
            $lastModifiedValue = $lastModifiedLiteral->getValue();
        } else {
            $lastModifiedValue = null;
        }

        $fedoraItemJson = array(
                            'o:job'         => array('o:id' => $this->job->getId()),
                            'o:item'        => array('o:id' => $itemId),
                            'uri'           => $uri,
                            'last_modified' => $lastModifiedValue
                          );

        if ($fedoraItem) {
            $response = $this->api->update('fedora_items', $fedoraItem->id(), $fedoraItemJson);
            $this->updatedCount++;
        } else {
            $this->addedCount++;
            $response = $this->api->create('fedora_items', $fedoraItemJson);
        }
        
        if ($response->isError()) {
            throw new Exception\RuntimeException('There was an error during fedora item creation.');
        }
        
        foreach ($containers as $container) {
            $containerUri = $container->getUri();
            if ($containerUri != $uri) {
                $this->importContainer($containerUri);
            }
        }
    }

    public function resourceToJson(EasyRdf_Resource $resource)
    {
        $json = array();
        if ($this->itemSetId) {
            $json['o:item_set'] = array(array('o:id' => $this->itemSetId));
        }

        foreach ($resource->propertyUris() as $property) {
            $easyRdfProperty = new EasyRdf_Resource($property);
            $propertyId = $this->getPropertyId($easyRdfProperty);
            if (!$propertyId) {
                continue;
            }

            $literals = $resource->allLiterals($easyRdfProperty);
            foreach ($literals as $literal) {
                $json[$property][] = array(
                        '@value'      => (string) $literal,
                        '@lang'       => $literal->getLang(),
                        'property_id' => $propertyId,
                        'type'        => 'literal',
                        );
            }
            $objects = $resource->allResources($easyRdfProperty);
            foreach ($objects as $object) {
                $json[$property][] = array(
                        '@id'      => $object->getUri(),
                        'property_id' => $propertyId,
                        'type'        => 'uri',
                        );
            }
        }
        
        //tack on dcterms:identifier and bibo:uri
        $dctermsId = $this->getPropertyId('http://purl.org/dc/terms/identifier');
        $json['http://purl.org/dc/terms/identifier'][] = array(
                '@value'      => $resource->getUri(),
                'property_id' => $dctermsId,
                'type'        => 'literal',
                );
        $biboUri = $this->getPropertyId('http://purl.org/ontology/bibo/uri');
        $json['http://purl.org/ontology/bibo/uri'][] = array(
                '@id'         => $resource->getUri(),
                'property_id' => $biboUri,
                'type'        => 'uri',
                );
        return $json;
    }

    /**
     * Get the property id for an rdf property, if known in Omeka
     * 
     * @param string or EasyRdf_Resource $property
     */
    protected function getPropertyId($property) 
    {
        if (is_string($property)) {
            $property = new EasyRdf_Resource($property);
        }
        $propertyUri = $property->getUri();
        //work around fedora's use of dc11
        $propertyUri = str_replace('http://purl.org/dc/elements/1.1/', 'http://purl.org/dc/terms/', $propertyUri );
        $localName = $property->localName();
        $vocabUri = str_replace($localName, '', $propertyUri);
        
        if (isset($this->propertyUriIdMap[$propertyUri])) {
            return $this->propertyUriIdMap[$propertyUri];
        }
        $response = $this->api->search('properties', array('vocabulary_namespace_uri' => $vocabUri,
                                                           'local_name' => $localName
                                                     ));
        $propertyObjects = $response->getContent();
        if (count($propertyObjects) == 1) {
            $propertyObject = $propertyObjects[0];
            $this->propertyUriIdMap[$propertyUri] = $propertyObject->id();
            return $this->propertyUriIdMap[$propertyUri];
        }
        return false;
    }
}