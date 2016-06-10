<?php
namespace FedoraConnector\Form;

use Omeka\Api\Exception\NotFoundException;
use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $translator = $this->getTranslator();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        
        try {
            $hasFedoraVocab = $api->read('vocabularies', array('namespaceUri' => 'http://fedora.info/definitions/v4/repository#' ));
        } catch (NotFoundException $e) {
            $hasFedoraVocab = false;
        }
        
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

        try {
            $hasLdpVocab = $api->read('vocabularies', array('namespaceUri' => 'http://www.w3.org/ns/ldp#' ));
        } catch (NotFoundException $e) {
            $hasLdpVocab = false;
        }
        
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