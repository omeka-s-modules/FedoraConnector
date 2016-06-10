<?php
namespace FedoraConnector\Service\Form;

use FedoraConnector\Form\ConfigForm;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $elements)
    {
        $form = new ConfigForm(null, $this->options);
        return $form;
    }
}
