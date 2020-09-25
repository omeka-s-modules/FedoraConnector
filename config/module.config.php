<?php
return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/FedoraConnector/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'fedora_items' => 'FedoraConnector\Api\Adapter\FedoraItemAdapter',
            'fedora_imports' => 'FedoraConnector\Api\Adapter\FedoraImportAdapter',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'FedoraConnector\Controller\Index' => 'FedoraConnector\Controller\IndexController',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH . '/modules/FedoraConnector/view',
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            OMEKA_PATH . '/modules/FedoraConnector/src/Entity',
        ],
    ],
    'form_elements' => [
        'factories' => [
            'FedoraConnector\Form\ImportForm' => 'FedoraConnector\Service\Form\ImportFormFactory',
            'FedoraConnector\Form\ConfigForm' => 'FedoraConnector\Service\Form\ConfigFormFactory',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'fedora-connector' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/fedora-connector',
                            'defaults' => [
                                '__NAMESPACE__' => 'FedoraConnector\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'past-imports' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/past-imports',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'FedoraConnector\Controller',
                                        'controller' => 'Index',
                                        'action' => 'past-imports',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Fedora Connector', // @translate
                'route' => 'admin/fedora-connector',
                'resource' => 'FedoraConnector\Controller\Index',
                'pages' => [
                    [
                        'label' => 'Import', // @translate
                        'route' => 'admin/fedora-connector',
                        'resource' => 'FedoraConnector\Controller\Index',
                    ],
                    [
                        'label' => 'Past Imports', // @translate
                        'route' => 'admin/fedora-connector/past-imports',
                        'controller' => 'Index',
                        'action' => 'past-imports',
                        'resource' => 'FedoraConnector\Controller\Index',
                    ],
                ],
            ],
        ],
    ],
];
