<?php
namespace FedoraConnector\Service\Form;

use FedoraConnector\Form\ConfigForm;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $elements)
    {
        $form = new ConfigForm;
        $api = $elements->getServiceLocator()->get('Omeka\ApiManager');
        $form->setApi($api);
        return $form;
    }
}
