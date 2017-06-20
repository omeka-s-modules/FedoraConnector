<?php
namespace FedoraConnector\Form;

use Omeka\Form\Element\ItemSetSelect;
use Zend\Form\Form;
use Zend\Form\Element\Select;

class ImportForm extends Form
{
    public function init()
    {
        $this->add(array(
            'name' => 'container_uri',
            'type' => 'url',
            'options' => array(
                'label' => 'Fedora container URI', // @translate
                'info'  => 'The URI of the Fedora container' // @translate
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
                'label' => 'Import files into Omeka S', // @translate
                'info'  => 'If checked, original files will be imported into Omeka S. Otherwise, derivates will be displayed when possible, with links back to the original file in the Fedora repository.' // @translate
            )
        ));

        $this->add(array(
            'name' => 'comment',
            'type' => 'textarea',
            'options' => array(
                'label' => 'Comment', // @translate
                'info'  => 'A note about the purpose or source of this import' // @translate
            ),
            'attributes' => array(
                'id' => 'comment'
            )
        ));

        $this->add([
                'name'    => 'itemSet',
                'type'    => ItemSetSelect::class,
                'options' => [
                    'label' => 'Item set', // @translate
                    'info' => 'Optional. Import items into this item set.', // @translate
                    'empty_option' => 'Select item set', // @translate
                ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add(array(
            'name' => 'itemSet',
            'required' => false,
        ));
    }
}