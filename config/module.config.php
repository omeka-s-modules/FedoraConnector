<?php
return array(
    'api_adapters' => array(
        'invokables' => array(
            'fedora_items' => 'FedoraConnector\Api\Adapter\Entity\FedoraItemAdapter',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'FedoraConnector\Controller\Index' => 'FedoraConnector\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'template_path_stack'      => array(
            OMEKA_PATH . '/module/FedoraConnector/view',
        ),
    ),
    'entity_manager' => array(
        'is_dev_mode' => true,
        'mapping_classes_paths' => array(
            OMEKA_PATH . '/module/FedoraConnector/src/Model/Entity',
        ),
    ),

    'router' => array(
        'routes' => array(
            'fedora-connector' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin/fedora-connector',
                    'defaults' => array(
                        '__NAMESPACE__' => 'FedoraConnector\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation' => array(
        'admin' => array(
            array(
                'label'      => 'Fedora Connector',
                'route'      => 'fedora-connector',
                'resource'   => 'FedoraConnector\Controller\Index',
                'pages'      => array(
                    array(
                        'label'      => 'Import',
                        'route'      => 'fedora-connector/default',
                        'resource'   => 'FedoraConnector\Controller\Index',
                    ),
                    array(
                        'label'      => 'Past Imports',
                        'route'      => 'fedora-connector/default',
                        'controller' => 'Index',
                        'action'     => 'past-imports',
                        'resource'   => 'FedoraConnector\Controller\Index',
                    ),
                ),
            ),
        ),
    )
);
