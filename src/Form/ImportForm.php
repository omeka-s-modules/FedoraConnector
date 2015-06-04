<?php
namespace FedoraConnector\Form;

use Omeka\Form\AbstractForm;
use Omeka\Form\Element\ResourceSelect;
use Zend\Validator\Callback;

class ImportForm extends AbstractForm
{
    public function buildForm()
    {
        $translator = $this->getTranslator();
        
        $this->add(array(
            'name' => 'container_uri',
            'type' => 'text',
            'options' => array(
                'label' => $translator->translate('Fedora Container URI'),
                'info'  => $translator->translate('The URI of the Fedora Container')
            ),
            'attributes' => array(
                'id' => 'container_uri'
            )
        ));
        
        $this->add(array(
            'name' => 'comment',
            'type' => 'textarea',
            'options' => array(
                'label' => $translator->translate('Comment'),
                'info'  => $translator->translate('A note about the purpose or source of this import.')
            ),
            'attributes' => array(
                'id' => 'comment'
            )
        ));
    }
}