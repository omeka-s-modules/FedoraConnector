<?php
namespace FedoraConnector\Form;

use Omeka\Form\Element\ItemSetSelect;
use Laminas\Form\Form;

class ImportForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'container_uri',
            'type' => 'url',
            'options' => [
                'label' => 'Fedora container URI', // @translate
                'info' => 'The URI of the Fedora container for the repository, collection or resource to import.', // @translate
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
            'attributes' => [
                'id' => 'ingest-files',
            ],
        ]);

        $this->add([
            'name' => 'ignore_parent',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Ignore parent container', // @translate
                'info' => 'If checked, only descendents of the container at URI above will be imported as items--not the parent container itself.', // @translate
            ],
            'attributes' => [
                'id' => 'ignore-parent',
            ],
        ]);

        $this->add([
            'name' => 'only_direct_children',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Import direct descendents only', // @translate
                'info' => 'If checked, only direct descendents of the container at URI above will be imported (i.e. no children of children). Otherwise, all resources below container will be recursively imported', // @translate
            ],
            'attributes' => [
                'id' => 'only_direct_children',
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
                'attributes' => [
                    'id' => 'item-set',
                ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'itemSet',
            'required' => false,
        ]);
    }
}
