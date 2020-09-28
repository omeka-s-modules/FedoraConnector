<?php
namespace FedoraConnector\Service\Form;

use FedoraConnector\Form\ConfigForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new ConfigForm;
        $api = $container->get('Omeka\ApiManager');
        $form->setApi($api);
        return $form;
    }
}
