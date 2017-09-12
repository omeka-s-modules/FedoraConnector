<?php
namespace FedoraConnector\Job;

use Omeka\Job\AbstractJob;
use EasyRdf_Graph;
use EasyRdf_Resource;
use EasyRdf_Namespace;

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
        $comment = $this->getArg('comment');
        $fedoraImportJson = [
                            'o:job' => ['o:id' => $this->job->getId()],
                            'comment' => $comment,
                            'added_count' => 0,
                            'updated_count' => 0,
                          ];
        $response = $this->api->create('fedora_imports', $fedoraImportJson);
        $importRecordId = $response->getContent()->id();

        $this->addedCount = 0;
        $this->updatedCount = 0;

        $this->propertyUriIdMap = [];
        $this->client = $this->getServiceLocator()->get('Omeka\HttpClient');
        $this->client->setHeaders(['Prefer' => 'return=representation; include="http://fedora.info/definitions/v4/repository#EmbedResources"']);
        $uri = $this->getArg('container_uri');
        $this->itemSetId = $this->getArg('itemSet', false);
        //importContainer calls itself on all child containers
        $this->importContainer($uri);

        $fedoraImportJson = [
                            'o:job' => ['o:id' => $this->job->getId()],
                            'comment' => $comment,
                            'added_count' => $this->addedCount,
                            'updated_count' => $this->updatedCount,
                          ];
        $response = $this->api->update('fedora_imports', $importRecordId, $fedoraImportJson);
    }

    public function importContainer($uri)
    {
        //see if the item has already been imported
        $response = $this->api->search('fedora_items', ['uri' => $uri]);
        $content = $response->getContent();
        if (empty($content)) {
            $fedoraItem = false;
            $omekaItem = false;
        } else {
            $fedoraItem = $content[0];
            $omekaItem = $fedoraItem->item();
        }

        $this->client->setUri($uri);
        $response = $this->client->send();
        $rdf = $response->getBody();
        EasyRdf_Namespace::set('fedora', 'http://fedora.info/definitions/v4/repository#');
        EasyRdf_Namespace::set('ldp', 'http://www.w3.org/ns/ldp#');
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

        $lastModifiedProperty = new EasyRdf_Resource('http://fedora.info/definitions/v4/repository#lastModified');
        $lastModifiedLiteral = $containerToImport->getLiteral($lastModifiedProperty);
        if ($lastModifiedLiteral) {
            $lastModifiedValue = $lastModifiedLiteral->getValue();
        } else {
            $lastModifiedValue = null;
        }

        $fedoraItemJson = [
                            'o:job' => ['o:id' => $this->job->getId()],
                            'o:item' => ['o:id' => $itemId],
                            'uri' => $uri,
                            'last_modified' => $lastModifiedValue,
                          ];

        if ($fedoraItem) {
            $response = $this->api->update('fedora_items', $fedoraItem->id(), $fedoraItemJson);
            $this->updatedCount++;
        } else {
            $this->addedCount++;
            $response = $this->api->create('fedora_items', $fedoraItemJson);
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
        $json = [];
        if ($this->itemSetId) {
            $json['o:item_set'] = [['o:id' => $this->itemSetId]];
        }

        foreach ($resource->propertyUris() as $property) {
            $easyRdfProperty = new EasyRdf_Resource($property);
            $propertyId = $this->getPropertyId($easyRdfProperty);
            if (!$propertyId) {
                continue;
            }

            $literals = $resource->allLiterals($easyRdfProperty);
            foreach ($literals as $literal) {
                $json[$property][] = [
                        '@value' => (string) $literal,
                        '@lang' => $literal->getLang(),
                        'property_id' => $propertyId,
                        'type' => 'literal',
                        ];
                // for files, add dcterms:title for the ebucore:filename
                if ($property == 'http://www.ebu.ch/metadata/ontologies/ebucore/ebucore#filename') {
                    $dctermsTitleId = $this->getPropertyId('http://purl.org/dc/terms/title');
                    $json[$property][] = [
                        '@value' => (string) $literal,
                        '@lang' => $literal->getLang(),
                        'property_id' => $dctermsTitleId,
                        'type' => 'literal',
                    ];
                }
            }
            $objects = $resource->allResources($easyRdfProperty);
            foreach ($objects as $object) {
                $json[$property][] = [
                        '@id' => $object->getUri(),
                        'property_id' => $propertyId,
                        'type' => 'uri',
                        ];
            }
        }

        $types = $resource->typesAsResources();
        foreach ($types as $index => $type) {
            $prefix = $type->prefix();
            if ($prefix == 'fedora' || $prefix == 'ldp' || empty($type)) {
                continue;
            }
            $classId = $this->getClassId($type);
            if ($classId) {
                $json['o:resource_class']['o:id'] = $classId;
                break;
            }
        }

        //tack on dcterms:identifier and bibo:uri
        $dctermsId = $this->getPropertyId('http://purl.org/dc/terms/identifier');
        $json['http://purl.org/dc/terms/identifier'][] = [
                '@value' => $resource->getUri(),
                'property_id' => $dctermsId,
                'type' => 'literal',
                ];
        $biboUri = $this->getPropertyId('http://purl.org/ontology/bibo/uri');
        $json['http://purl.org/ontology/bibo/uri'][] = [
                '@id' => $resource->getUri(),
                'property_id' => $biboUri,
                'type' => 'uri',
                ];
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
        $propertyUri = str_replace('http://purl.org/dc/elements/1.1/', 'http://purl.org/dc/terms/', $propertyUri);
        $localName = $property->localName();
        $vocabUri = str_replace($localName, '', $propertyUri);

        if (isset($this->propertyUriIdMap[$propertyUri])) {
            return $this->propertyUriIdMap[$propertyUri];
        }
        $response = $this->api->search('properties', ['vocabulary_namespace_uri' => $vocabUri,
                                                           'local_name' => $localName,
                                                     ]);
        $propertyObjects = $response->getContent();
        if (count($propertyObjects) == 1) {
            $propertyObject = $propertyObjects[0];
            $this->propertyUriIdMap[$propertyUri] = $propertyObject->id();
            return $this->propertyUriIdMap[$propertyUri];
        }
        return false;
    }

    protected function getClassId($class)
    {
        if (is_string($class)) {
            $class = new EasyRdf_Resource($class);
        }
        $classUri = $class->getUri();
        $localName = $class->localName();
        $vocabUri = str_replace($localName, '', $classUri);
        $response = $this->api->search('resource_classes', ['vocabulary_namespace_uri' => $vocabUri,
                                                                 'local_name' => $localName,
                                                           ]);
        $classObjects = $response->getContent();
        if (count($classObjects) == 1) {
            $classObject = $classObjects[0];
            return $classObject->id();
        }
        return false;
    }
}
