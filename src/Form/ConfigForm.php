<?php
namespace FedoraConnector\Form;

use Omeka\Api\Exception\NotFoundException;
use Laminas\Form\Form;

class ConfigForm extends Form
{
    protected $api;

    public function init()
    {
        $api = $this->getApi();
        try {
            $hasFedoraVocab = $api->read('vocabularies', ['namespaceUri' => 'http://fedora.info/definitions/v4/repository#' ]);
        } catch (NotFoundException $e) {
            $hasFedoraVocab = false;
        }

        if ($hasFedoraVocab) {
            $info = 'The Fedora Vocabulary is already installed.'; // @translate
        } else {
            $info = 'Import the Fedora Vocabulary.'; // @translate
        }

        $this->add([
                        'type' => 'checkbox',
                        'name' => 'import_fedora',
                        'options' => [
                                    'label' => 'Import Fedora Vocabulary', // @translate
                                    'info' => $info,
                                ],
                        'attributes' => [
                                    'checked' => $hasFedoraVocab ? 'checked' : '',
                                    'disabled' => $hasFedoraVocab ? 'disabled' : '',
                                    'id' => 'import-fedora',
                                ],
                    ]);

        try {
            $hasLdpVocab = $api->read('vocabularies', ['namespaceUri' => 'http://www.w3.org/ns/ldp#' ]);
        } catch (NotFoundException $e) {
            $hasLdpVocab = false;
        }

        if ($hasLdpVocab) {
            $info = 'The Linked Data Platform Vocabulary is already installed.'; // @translate
        } else {
            $info = 'Import the Linked Data Platform Vocabulary.'; // @translate
        }

        $this->add([
                        'type' => 'checkbox',
                        'name' => 'import_ldp',
                        'options' => [
                                    'label' => 'Import Linked Data Platform Vocabulary', // @translate
                                    'info' => $info,
                                ],
                        'attributes' => [
                                    'checked' => $hasLdpVocab ? 'checked' : '',
                                    'disabled' => $hasLdpVocab ? 'disabled' : '',
                                    'id' => 'import-ldp',
                                ],
                    ]);
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
