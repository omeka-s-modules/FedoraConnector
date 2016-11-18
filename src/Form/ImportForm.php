<?php
namespace FedoraConnector\Form;

use Omeka\Form\Element\ResourceSelect;
use Omeka\Form\Element\ItemSetSelect;
use Zend\Form\Form;
use Zend\Validator\Callback;
use Zend\Form\Element\Select;

class ImportForm extends Form
{
    public function init()
    {
        $this->add(array(
            'name' => 'container_uri',
            'type' => 'url',
            'options' => array(
                'label' => 'Fedora Container URI', // @translate
                'info'  => 'The URI of the Fedora Container' // @translate
            ),
            'attributes' => array(
                'id' => 'container_uri',
                'required' => true
            )
        ));

        $this->add(array(
            'name' => 'ingest_files',
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Import files into Omeka', // @translate
                'info'  => 'If checked, original files will be imported into Omeka. Otherwise, derivates will be displayed when possible, with links back to the original file in the repository.' // @translate
            )
        ));

        $this->add(array(
            'name' => 'comment',
            'type' => 'textarea',
            'options' => array(
                'label' => 'Comment', // @translate
                'info'  => 'A note about the purpose or source of this import.' // @translate
            ),
            'attributes' => array(
                'id' => 'comment'
            )
        ));

        //$serviceLocator = $this->getServiceLocator();
        //$auth = $serviceLocator->get('Omeka\AuthenticationService');


        $this->add([
                'name'    => 'itemSet',
                'type'    => ItemSetSelect::class,
                'options' => [
                    'label' => 'Item Set', // @translate
                    'info' => 'Optional. Import items into this item set.', // @translate
                    'empty_option' => 'Select Item Set', // @translate
                    /*
                    'resource_value_options' => [
                        'resource' => 'item_sets',
                        'query' => [],
                        'option_text_callback' => function ($itemSet) {
                            return $itemSet->displayTitle();
                        },
                    ], */
                ],
        ]);
/*
        $itemSetSelect = new ResourceSelect($serviceLocator);
        $itemSetSelect->setName('itemSet')
            ->setLabel('Import into') // @translate
            ->setOption('info', 'Optional. Import items into this item set.')
            ->setEmptyOption('Select Item Set...')
            ->setResourceValueOptions(
                'item_sets',
                array('owner_id' => $auth->getIdentity()),
                function ($itemSet, $serviceLocator) {
                    return $itemSet->displayTitle('[no title]');
                }
            );
        $this->add($itemSetSelect);
*/
        $inputFilter = $this->getInputFilter();
        $inputFilter->add(array(
            'name' => 'itemSet',
            'required' => false,
        ));
    }
}