<?php
namespace FedoraConnector\Job;

use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use FedoraConnector\Model\Entity\FedoraItem;
use Zend\Http\Client;
use EasyRdf_Graph;
use EasyRdf_Resource;

class Import extends AbstractJob
{
    protected $client;
    
    protected $propertyUriIdMap;
    
    protected $api;
    
    public function perform()
    {
        $this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $this->propertyUriIdMap = array();
        $this->client = $this->getServiceLocator()->get('Omeka\HttpClient');
        $this->client->setHeaders(array('Prefer' => 'return=representation; include="http://fedora.info/definitions/v4/repository#EmbedResources"'));
        
        $uri = $this->getArg('container_uri');
    }
    
    public function importContainer($uri)
    {
        $response = $this->client->send($uri);
        $rdf = $response->getBody();
        $graph = new EasyRdf_Graph();
        $graph->parse($rdf);
        
        $containerToImport = $graph->resource($uri);
        $containers = $graph->allOfType("http://fedora.info/definitions/v4/repository#Container");
        $binaries = $graph->allOfType("http://fedora.info/definitions/v4/repository#Binary");
        
        $json = $this->resourceToJson($containerToImport);
        
        foreach ($binaries as $binary) {
            $mediaJson = $this->resourceToJson($binary);
            $mediaJson['o:type'] = 'url';
            $mediaJson['o:source'] = $binary->getUri();
            $mediaJson['ingest_url'] = $binary->getUri();
            $json['o:media'][] = $mediaJson;
        }
        $response = $this->api->create('items', $itemJson);
        if ($response->isError()) {
            echo 'error';
            print_r( $response->getErrors() );
            throw new Exception\RuntimeException('There was an error during item creation.');
        }
    }
    
    public function resourceToJson(EasyRdf_Resource $resource)
    {
        $json = array();
        foreach ($resource->propertyUris() as $property) {
            $propertyId = $this->getPropertyId($property);
            if (!$propertyId) {
                continue;
            }
            $easyRdfProperty = new EasyRdf_Resource($property);
            
            $literals = $resource->allLiterals($easyRdfProperty);
            foreach ($literals as $literal) {
                $json[$property][] = array(
                        '@value'      => $literal->getValue(),
                        '@lang'       => $literal->getLang(),
                        'property_id' => $propertyId
                        );
            }
            $resources = $resource->allResources($easyRdfProperty);
            foreach ($resources as $resource) {
                $json[$property][] = array(
                        '@id'      => $resource->getUri(),
                        'property_id' => $propertyId
                        );
            }
        }
        return $json;
    }
        
    protected function getPropertyId($property) 
    {
        if (isset($this->propertyUriIdMap[$property])) {
            return $this->propertyUriIdMap[$property];
        }
        $response = $this->api->read('properties', array('uri' => $property));
        $propertyObject = $response->getContent();
        if ($propertyObject) {
            $this->propertyUriIdMap[$property] = $propertyObject->id();
            return $this->propertyUriIdMap[$property];
        }
        return false;
    }
}