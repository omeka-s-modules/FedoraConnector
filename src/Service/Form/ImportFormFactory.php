<?php
namespace FedoraConnector\Service\Form;

use FedoraConnector\Form\ImportForm;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportFormFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $elements)
    {
        $form = new ImportForm;
        return $form;
    }
}
