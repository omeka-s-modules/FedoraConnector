<?php
namespace FedoraConnector\Service\Form;

use FedoraConnector\Form\ImportForm;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ImportFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new ImportForm;
        return $form;
    }
}
