<?php
return array(
    'api_adapters' => array(
        'invokables' => array(
            'fedora_items'   => 'FedoraConnector\Api\Adapter\FedoraItemAdapter',
            'fedora_imports' => 'FedoraConnector\Api\Adapter\FedoraImportAdapter'
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'FedoraConnector\Controller\Index' => 'FedoraConnector\Controller\IndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack'      => array(
            OMEKA_PATH . '/modules/FedoraConnector/view',
        ),
    ),
    'entity_manager' => array(
        'mapping_classes_paths' => array(
            OMEKA_PATH . '/modules/FedoraConnector/src/Entity',
        ),
    ),
    'form_elements' => [
        'factories' => [
            'FedoraConnector\Form\ImportForm' => 'FedoraConnector\Service\Form\ImportFormFactory',
            'FedoraConnector\Form\ConfigForm' => 'FedoraConnector\Service\Form\ConfigFormFactory',
        ],
    ],
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'fedora-connector' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/fedora-connector',
                            'defaults' => array(
                                '__NAMESPACE__' => 'FedoraConnector\Controller',
                                'controller'    => 'Index',
                                'action'        => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'past-imports' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route' => '/past-imports',
                                    'defaults' => array(
                                        '__NAMESPACE__' => 'FedoraConnector\Controller',
                                        'controller'    => 'Index',
                                        'action'        => 'past-imports',
                                    ),
                                )
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation' => array(
        'AdminModule' => array(
            array(
                'label'      => 'Fedora Connector',
                'route'      => 'admin/fedora-connector',
                'resource'   => 'FedoraConnector\Controller\Index',
                'pages'      => array(
                    array(
                        'label'      => 'Import',
                        'route'      => 'admin/fedora-connector',
                        'resource'   => 'FedoraConnector\Controller\Index',
                    ),
                    array(
                        'label'      => 'Past Imports',
                        'route'      => 'admin/fedora-connector/past-imports',
                        'controller' => 'Index',
                        'action'     => 'past-imports',
                        'resource'   => 'FedoraConnector\Controller\Index',
                    ),
                ),
            ),
        ),
    )
);
