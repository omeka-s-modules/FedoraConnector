<?php
namespace FedoraConnector\Form;

use Omeka\Form\Element\ItemSetSelect;
use Zend\Form\Form;
use Zend\Form\Element\Select;

class ImportForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'container_uri',
            'type' => 'url',
            'options' => [
                'label' => 'Fedora container URI', // @translate
                'info' => 'The URI of the Fedora container', // @translate
            ],
            'attributes' => [
                'id' => 'container_uri',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'ingest_files',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Import files into Omeka S', // @translate
                'info' => 'If checked, original files will be imported into Omeka S. Otherwise, derivates will be displayed when possible, with links back to the original file in the Fedora repository.', // @translate
            ],
        ]);

        $this->add([
            'name' => 'comment',
            'type' => 'textarea',
            'options' => [
                'label' => 'Comment', // @translate
                'info' => 'A note about the purpose or source of this import', // @translate
            ],
            'attributes' => [
                'id' => 'comment',
            ],
        ]);

        $this->add([
                'name' => 'itemSet',
                'type' => ItemSetSelect::class,
                'options' => [
                    'label' => 'Item set', // @translate
                    'info' => 'Optional. Import items into this item set.', // @translate
                    'empty_option' => 'Select item set', // @translate
                ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'itemSet',
            'required' => false,
        ]);
    }
}
