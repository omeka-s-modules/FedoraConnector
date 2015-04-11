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
        //$uri = 'http://localhost:8080/rest/ff/f0/c5/ab/fff0c5ab-72a0-4f49-88af-eb29778ea8d1';
        $this->importContainer($uri);
    }

    public function importContainer($uri)
    {
        $this->client->setUri($uri);
        $response = $this->client->send();
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
        $response = $this->api->create('items', $json);
        if ($response->isError()) {
            echo 'error';
            print_r( $response->getErrors() );
            throw new Exception\RuntimeException('There was an error during item creation.');
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
                        'property_id' => $propertyId
                        );
            }
            $objects = $resource->allResources($easyRdfProperty);
            foreach ($objects as $object) {
                $json[$property][] = array(
                        '@id'      => $object->getUri(),
                        'property_id' => $propertyId
                        );
            }
        }
        
        //tack on dcterms:identifier and bibo:uri
        $dctermsId = $this->getPropertyId('http://purl.org/dc/terms/identifier');
        $json['http://purl.org/dc/terms/identifier'][] = array(
                '@value'      => $resource->getUri(),
                'property_id' => $dctermsId
                );
        $biboUri = $this->getPropertyId('http://purl.org/ontology/bibo/uri');
        $json['http://purl.org/ontology/bibo/uri'][] = array(
                '@id'         => $resource->getUri(),
                'property_id' => $biboUri
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