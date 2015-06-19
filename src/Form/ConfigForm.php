<?php
namespace FedoraConnector\Form;

use Omeka\Form\AbstractForm;

class ConfigForm extends AbstractForm
{
    public function buildForm()
    {
        $translator = $this->getTranslator();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        
        $hasFedoraVocab = $api->read('vocabularies', array('namespace_uri' => 'http://fedora.info/definitions/v4/repository#' ));
        if ($hasFedoraVocab) {
            $info = $translator->translate('The Fedora Vocabulary is already installed.');
        } else {
            $info = $translator->translate('Import the Fedora Vocabulary.');
        }
        
        $this->add(array (
                        'type' => 'checkbox',
                        'name' => 'import_fedora',
                        'options' => array (
                                    'label' => $translator->translate('Import Fedora Vocabulary'),
                                    'info'  => $info
                                ),
                        'attributes' => array (
                                    'checked'  => $hasFedoraVocab ? 'checked' : '',
                                    'disabled' => $hasFedoraVocab ? 'disabled' : ''
                                )
                    ));

        $hasLdpVocab = $api->read('vocabularies', array('namespace_uri' => 'http://www.w3.org/ns/ldp#' ));
        if ($hasLdpVocab) {
            $info = $translator->translate('The Linked Data Platform Vocabulary is already installed.');
        } else {
            $info = $translator->translate('Import the Linked Data Platform Vocabulary.');
        }
                
        $this->add(array (
                        'type' => 'checkbox',
                        'name' => 'import_ldp',
                        'options' => array (
                                    'label' => $translator->translate('Import Linked Data Platform Vocabulary'),
                                    'info'  => $info
                                ),
                        'attributes' => array (
                                    'checked'  => $hasLdpVocab ? 'checked' : '',
                                    'disabled' => $hasLdpVocab ? 'disabled' : ''
                                )
                    ));
    }
}