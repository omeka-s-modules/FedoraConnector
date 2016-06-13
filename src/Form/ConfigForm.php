<?php
namespace FedoraConnector\Form;

use Omeka\Api\Exception\NotFoundException;
use Zend\Form\Form;

class ConfigForm extends Form
{
    
    protected $api;
    
    public function init()
    {
        $api = $this->getApi();
        try {
            $hasFedoraVocab = $api->read('vocabularies', array('namespaceUri' => 'http://fedora.info/definitions/v4/repository#' ));
        } catch (NotFoundException $e) {
            $hasFedoraVocab = false;
        }
        
        if ($hasFedoraVocab) {
            $info = 'The Fedora Vocabulary is already installed.'; // @translate
        } else {
            $info = 'Import the Fedora Vocabulary.'; // @translate
        }
        
        $this->add(array (
                        'type' => 'checkbox',
                        'name' => 'import_fedora',
                        'options' => array (
                                    'label' => 'Import Fedora Vocabulary', // @translate
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
            $info = 'The Linked Data Platform Vocabulary is already installed.'; // @translate
        } else {
            $info = 'Import the Linked Data Platform Vocabulary.'; // @translate
        }
                
        $this->add(array (
                        'type' => 'checkbox',
                        'name' => 'import_ldp',
                        'options' => array (
                                    'label' => 'Import Linked Data Platform Vocabulary', // @translate
                                    'info'  => $info
                                ),
                        'attributes' => array (
                                    'checked'  => $hasLdpVocab ? 'checked' : '',
                                    'disabled' => $hasLdpVocab ? 'disabled' : ''
                                )
                    ));
    }
    
    public function setApi($api)
    {
        $this->api = $api;
    }
    
    public function getApi()
    {
        return $this->api;
    }
}
