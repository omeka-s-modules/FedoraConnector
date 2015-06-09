<?php
namespace FedoraConnector\Form;

use Omeka\Form\AbstractForm;
use Omeka\Form\Element\ResourceSelect;
use Zend\Validator\Callback;
use Zend\Form\Element\Select;

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
        
        
        
        $serviceLocator = $this->getServiceLocator();
        $auth = $serviceLocator->get('Omeka\AuthenticationService');
        
        $itemSetSelect = new ResourceSelect($serviceLocator);
        $itemSetSelect->setName('itemSet')
            ->setLabel('Import into')
            ->setOption('info', $translator->translate('Optional. Import items into this item set.'))
            ->setEmptyOption('Select Item Set...')
            ->setResourceValueOptions(
                'item_sets',
                array('owner_id' => $auth->getIdentity()),
                function ($itemSet, $serviceLocator) {
                    return $itemSet->displayTitle('[no title]');
                }
            );
        $this->add($itemSetSelect);
        
        $inputFilter = $this->getInputFilter();
        $inputFilter->add(array(
            'name' => 'itemSet',
            'required' => false,
        ));
    }
}