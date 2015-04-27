<?php
namespace FedoraConnector\Form;

use Omeka\Form\AbstractForm;

class ConfigForm extends AbstractForm
{
    public function buildForm()
    {
        $translator = $this->getTranslator();

        $this->add(array (
                        'type' => 'checkbox',
                        'name' => 'import_fedora',
                        'options' => array (
                                    'label' => 'Import Fedora Vocabulary',
                                    'info'  => 'Import the Fedora Vocabulary into Omeka'
                                )
                    ));
        $this->add(array (
                        'type' => 'checkbox',
                        'name' => 'import_ldp',
                        'options' => array (
                                    'label' => 'Import Linked Data Platform Vocabulary',
                                    'info'  => 'Import the Linked Data Platform Vocabulary into Omeka'
                                )
                    ));
    }
}