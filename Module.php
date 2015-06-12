<?php
namespace FedoraConnector;

use Omeka\Module\AbstractModule;
use Omeka\Entity\Job;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;
use Zend\Mvc\Controller\AbstractController;
use Zend\EventManager\SharedEventManagerInterface;
use FedoraConnector\Form\ConfigForm;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec("CREATE TABLE fedora_item (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, job_id INT NOT NULL, uri VARCHAR(255) NOT NULL, last_modified DATETIME NULL, UNIQUE INDEX UNIQ_D02FFFF9126F525E (item_id), INDEX IDX_D02FFFF9BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;");
        $connection->exec("CREATE TABLE fedora_import (id INT AUTO_INCREMENT NOT NULL, job_id INT NOT NULL, undo_job_id INT DEFAULT NULL, added_count INT NOT NULL, updated_count INT NOT NULL, comment VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_EA775FC8BE04EA9 (job_id), UNIQUE INDEX UNIQ_EA775FC84C276F75 (undo_job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;");
        $connection->exec("ALTER TABLE fedora_item ADD CONSTRAINT FK_D02FFFF9126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE;");
        $connection->exec("ALTER TABLE fedora_item ADD CONSTRAINT FK_D02FFFF9BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);");
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec("ALTER TABLE fedora_item DROP FOREIGN KEY FK_D02FFFF9126F525E;");
        $connection->exec("ALTER TABLE fedora_item DROP FOREIGN KEY FK_D02FFFF9BE04EA9;");
        $connection->exec('DROP TABLE fedora_item');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager) 
    {
        $sharedEventManager->attach(
                'Omeka\Controller\Admin\Item',
                'view.show.after',
                array($this, 'showSource')
                );
    }
    /**
     * Get this module's configuration form.
     *
     * @param ViewModel $view
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $form = new ConfigForm($this->getServiceLocator());
        $html = $renderer->formElements($form);
        return $html;
    }

    /**
     * Handle this module's configuration form.
     *
     * @param AbstractController $controller
     * @return bool False if there was an error during handling
     */
    public function handleConfigForm(AbstractController $controller)
    {
        $importer = $this->getServiceLocator()->get('Omeka\RdfImporter');
        $data = $controller->params()->fromPost();
        $success = true;
        if ($data['import_fedora'] == 1 ) {
            $fedoraImportData = array(
                    'o:prefix'        => 'fedora',
                    'o:namespace_uri' => 'http://fedora.info/definitions/v4/repository#',
                    'o:label'         => 'Fedora Vocabulary',
                    'o:comment'       => 'Vocabulary for a Fedora Repository',
                    'file'            => OMEKA_PATH . '/module/FedoraConnector/data/repository.rdf'
                    );
            
            $response = $importer->import(
                'file', $fedoraImportData, array('file' => OMEKA_PATH . '/module/FedoraConnector/data/repository.rdf')
            );
            if ($response->isError()) {
                $success = false;
            }
        }

        if ($data['import_ldp'] == 1) {
            $ldpImportData = array(
                    'o:prefix'        => 'ldp',
                    'o:namespace_uri' => 'http://www.w3.org/ns/ldp#',
                    'o:label'         => 'Linked Data Platform Vocabulary',
                    'o:comment'       => 'Vocabulary for a Linked Data Platform. Used by Fedora',
                    'file'            => OMEKA_PATH . '/module/FedoraConnector/data/repository.rdf'
                    );
            $response = $importer->import(
                'file', $ldpImportData, array('file' => OMEKA_PATH . '/module/FedoraConnector/data/ldp.rdf')
            );
            if ($response->isError()) {
                $success = false;
            }
        }
        return $success;
        
    }
    
    public function showSource($event) 
    {
        $view = $event->getTarget();
        $item = $view->item;
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $response = $api->search('fedora_items', array('item_id' => $item->id()));
        if ($response->isError()) {

        }
        $fedoraItems = $response->getContent();
        if ($fedoraItems) {
            $fedoraItem = $fedoraItems[0];
            echo '<h3>' . $view->translate('Original')  . '</h3>';
            echo '<p>' . $view->translate('Last Modified') . ' ' . $view->i18n()->dateFormat($fedoraItem->lastModified()) . '</p>';
            echo '<p><a href="' . $fedoraItem->uri() . '">' . $view->translate('Link') . '</a></p>';
        }
    }
}