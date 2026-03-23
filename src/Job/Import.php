<?php
namespace FedoraConnector\Job;

use Omeka\Job\AbstractJob;
use EasyRdf\Graph;
use Laminas\Http\Response as Response;
use EasyRdf\Resource as RdfResource;
use EasyRdf\RdfNamespace;

class Import extends AbstractJob
{
    protected $client;

    protected $propertyUriIdMap;

    protected $api;

    protected $logger;

    protected $resourceTemplateId;

    protected $itemSetArray;

    protected $itemSites;

    protected $addedCount;

    protected $updatedCount;

    protected $addedFiles;

    protected $visitedUris = [];

    public function perform()
    {
        $this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
        $comment = $this->getArg('comment');
        $fedoraImportJson = [
                            'o:job' => ['o:id' => $this->job->getId()],
                            'comment' => $comment,
                            'added_count' => 0,
                            'updated_count' => 0,
                            'added_files' => 0,
                          ];
        $response = $this->api->create('fedora_imports', $fedoraImportJson);
        $importRecordId = $response->getContent()->id();

        $this->addedCount = 0;
        $this->updatedCount = 0;
        $this->addedFiles = 0;

        $this->propertyUriIdMap = [];
        $this->client = $this->getServiceLocator()->get('Omeka\HttpClient');
        $this->client->setHeaders([
            'Accept' => 'application/ld+json',
            'Prefer' => 'return=representation; include="http://www.w3.org/ns/ldp#PreferContainment http://fedora.info/definitions/v4/repository#EmbedResources"'
        ]);
        $uri = $this->getArg('container_uri');
        $this->resourceTemplateId = (int) $this->getArg('resource_template', 0);
        $this->itemSetArray = $this->getArg('itemSets', false);
        $this->itemSiteArray = $this->getArg('itemSites', false);
        //importResource calls itself on all child containers
        $this->importResource($uri);

        $fedoraImportJson = [
                            'o:job' => ['o:id' => $this->job->getId()],
                            'comment' => $comment,
                            'added_count' => $this->addedCount,
                            'updated_count' => $this->updatedCount,
                            'added_files' => $this->addedFiles,
                          ];
        $response = $this->api->update('fedora_imports', $importRecordId, $fedoraImportJson);
    }

    public function importResource($uri)
    {
        if (isset($this->visitedUris[$uri])) {
            return;
        }
        $this->visitedUris[$uri] = true;

        if (preg_match('#/(pages|orderProxies|files)(/|$)#', $uri)) {
            return;
        }

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

        RdfNamespace::set('fedora', 'http://fedora.info/definitions/v4/repository#');
        RdfNamespace::set('ldp', 'http://www.w3.org/ns/ldp#');
        RdfNamespace::set('pcdm', 'http://pcdm.org/models#');
        RdfNamespace::set('ore', 'http://www.openarchives.org/ore/terms/');
        RdfNamespace::set('dcterms', 'http://purl.org/dc/terms/');
        RdfNamespace::set('schema', 'http://schema.org/');

        // Determine RDF format
        $contentType = $response->getHeaders()->get('Content-Type')->getFieldValue();
        if (strpos($contentType, 'json') === false && strpos($contentType, 'rdf') === false) {
            return;
        }
        $format = strpos($contentType, 'json') !== false ? 'jsonld' : null;

        $graph = new Graph();
        $graph->parse($rdf, $format);
        $normalizedUri = rtrim($uri, '/');
        $resource = $graph->resource($normalizedUri);

        $members = $this->getMembers($resource, $graph, $normalizedUri);

        $isTopLevel = ($uri === $this->getArg('container_uri'));

        // if ignore_parent set, don't import parent object
        if (!($this->getArg('ignore_parent') && $isTopLevel)) {
            $json = $this->resourceToJson($resource);

            if ($this->getArg('ingest_files')) {
                // parse out and ingest binary files
                $binaries = $this->collectBinariesRecursively($members);
                foreach ($binaries as $binary) {
                    $mediaJson = [];
                    // build new graph for binary metadata
                    $metadataUri = rtrim($binary, '/') . '/fcr:metadata';
                    $this->client->setUri($metadataUri);
                    $response = $this->client->send();
                    $graph = new Graph();
                    $graph->parse($response->getBody(), 'jsonld');
                    $mediaJson = $this->resourceToJson($graph->resource($binary));
                    $mediaJson['o:ingester'] = 'url';
                    $mediaJson['o:source'] = $binary;
                    $mediaJson['ingest_url'] = $binary;
                    $json['o:media'][] = $mediaJson;
                }
            }

            if ($omekaItem) {
                // keep existing item sets/sites, add any new item sets/sites
                $existingItem = $this->api->search('items', ['id' => $omekaItem->id()])->getContent();

                $existingItemSets = array_keys($existingItem[0]->itemSets()) ?: [];
                $newItemSets = $json['o:item_set'] ?: [];
                $json['o:item_set'] = array_merge($existingItemSets, $newItemSets);

                $existingItemSites = array_keys($existingItem[0]->sites()) ?: [];
                $newItemSites = $json['o:site'] ?: [];
                $json['o:site'] = array_merge($existingItemSites, $newItemSites);

                // Continue with next item on error
                try {
                    $response = $this->api->update('items', $omekaItem->id(), $json);
                } catch (\Exception $e) {
                    $this->logger->err((string) $e);
                    return;
                }
                $itemId = $omekaItem->id();

                // Count successfully added files
                if ($this->getArg('ingest_files')) {
                    foreach ($response->getContent()->media() as $media) {
                        if ($media->hasOriginal()) {
                            $this->addedFiles++;
                        }
                    }
                }
            } else {
                // Continue with next item on error
                try {
                    $response = $this->api->create('items', $json);
                } catch (\Exception $e) {
                    $this->logger->err((string) $e);
                    return;
                }
                $itemId = $response->getContent()->id();

                // Count successfully added files
                if ($this->getArg('ingest_files')) {
                    $itemRepresentation = $this->api->read('items', $itemId)->getContent();
                    foreach ($itemRepresentation->media() as $media) {
                        if ($media->hasOriginal()) {
                            $this->addedFiles++;
                        }
                    }
                }
            }
            $json['o:media'] = [];

            $lastModified = $resource->getLiteral('fedora:lastModified');
            $lastModifiedValue = $lastModified ? $lastModified->getValue() : null;

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
        }
        
        // if only_direct_children set, only recurse one level down from top
        if ($this->getArg('only_direct_children') && !$isTopLevel) {
            return;
        }
        $mediaItems = [];

        foreach ($members as $member) {
            $memberUri = rtrim($member->getUri(), '/');

            if ($this->isBinaryUri($memberUri)) {
                continue; // handle later in media ingestion
            }
        
            // Don't recurse Fedora admin links
            if (preg_match('#/(pages|orderProxies|files)(/|$)#', $memberUri)) {
                continue;
            }

            $this->importResource($memberUri);
        }
    }

    public function resourceToJson(RdfResource $resource)
    {
        $json = [];
        if ($this->itemSetArray) {
            foreach ($this->itemSetArray as $itemSet) {
                $itemSets[] = $itemSet;
            }
            $json['o:item_set'] = $itemSets;
        }

        if ($this->itemSiteArray) {
            foreach ($this->itemSiteArray as $itemSite) {
                $itemSites[] = $itemSite;
            }
            $json['o:site'] = $itemSites;
        } else {
            $json['o:site'] = [];
        }

        if ($this->resourceTemplateId) {
            $json['o:resource_template']['o:id'] = (int) $this->resourceTemplateId;
        }

        foreach ($resource->propertyUris() as $property) {
            $easyRdfProperty = new RdfResource($property);
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
     * Get children of a Fedora resource.
     * Handles direct membership, IndirectContainers with proxies, and URI-based inference.
     *
     * @param RdfResource $resource
     * @param Graph $graph The full graph containing the resource
     * @param string $uri The resource URI (parent)
     * @return EasyRdf\Resource[] Array of child resources
     */
    protected function getMembers(RdfResource $resource, Graph $graph, string $uri): array
    {
        $members = [];

        $types = $resource->typesAsResources();
        $isIndirectContainer = false;
        foreach ($types as $type) {
            if ($type->getUri() === 'http://www.w3.org/ns/ldp#IndirectContainer') {
                $isIndirectContainer = true;
                break;
            }
        }

        if ($isIndirectContainer) {
            foreach ($graph->resources() as $res) {
                $proxyFor = $res->allResources('http://www.openarchives.org/ore/terms#proxyFor');
                foreach ($proxyFor as $realMember) {
                    // Check if this proxy belongs to our container
                    $proxyContainer = $res->get('http://www.openarchives.org/ore/terms#proxyIn');
                    $proxyContainerUri = $proxyContainer ? $proxyContainer->getUri() : null;
                    if ($proxyContainerUri === $uri) {
                        $members[] = $realMember;
                    }
                }
            }
        }

        $members = array_merge($members, $resource->allResources('ldp:contains'));
        $members = array_merge($members, $resource->allResources('pcdm:hasMember'));
        $members = array_merge($members, $resource->allResources('schema:hasPart'));
        $members = array_merge($members, $resource->allResources('ore:aggregates'));

        $members = array_unique($members, SORT_REGULAR);

        return $members;
    }

    protected function loadGraphForUri($uri)
    {
        $this->client->setUri($uri);
        $response = $this->client->send();

        // Detect if RDF
        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'json') === false) {
            return new Graph();
        }

        $graph = new Graph();
        $graph->parse($response->getBody(), 'jsonld');
        return $graph;
    }

    /**
     * Recursively collect all binaries under a set of members
     *
     * @param EasyRdf\Resource $members
     * @return EasyRdf\Resource
     */
    protected function collectBinariesRecursively(array $members): array
    {
        $binaries = [];
        foreach ($members as $member) {
            $uri = $member->getUri();

            // HEAD request to check Content-Type
            $this->client->setUri($uri);
            $response = $this->client->send();
            $contentType = $response->getHeaders()->get('Content-Type')->getFieldValue();
            $isBinary = strpos($contentType, 'application/ld+json') === false && strpos($contentType, 'rdf') === false;
            if ($isBinary) {
                $binaries[] = $uri;
                continue;
            }

            // Parse RDF of child to get its members
            $rdf = $response->getBody();
            $format = strpos($contentType, 'json') !== false ? 'jsonld' : null;
            $graph = new Graph();
            $graph->parse($rdf, $format);
            $res = $graph->resource($uri);

            $childMembers = array_merge(
                $res->allResources('schema:hasPart'),
                $res->allResources('ldp:contains'),
                $res->allResources('pcdm:hasMember'),
                $res->allResources('ore:aggregates')
            );

            $binaries = array_merge($binaries, $this->collectBinariesRecursively($childMembers));
        }
        return array_values(array_unique($binaries, SORT_REGULAR));
    }
    
    protected function isBinaryUri(string $uri): bool
    {
        return preg_match('/\.(tif|tiff|jpg|jpeg|png|pdf)$/i', $uri);
    }

    /**
     * Get the property id for an rdf property, if known in Omeka
     *
     * @param string|RdfResource $property
     */
    protected function getPropertyId($property)
    {
        if (is_string($property)) {
            $property = new RdfResource($property);
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
            $class = new RdfResource($class);
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
