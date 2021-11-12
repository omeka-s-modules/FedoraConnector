<?php
namespace FedoraConnector\Service\Form;

use FedoraConnector\Form\ImportForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ImportFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new ImportForm;
        $form->setUserSettings($services->get('Omeka\Settings\User'));
        $form->setAuthenticationService($services->get('Omeka\AuthenticationService'));
        $form->setApiManager($services->get('Omeka\ApiManager'));
        return $form;
    }
}
