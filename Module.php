<?php
namespace FedoraConnector;

use Omeka\Module\AbstractModule;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Composer\Semver\Comparator;
use FedoraConnector\Form\ConfigForm;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(
            null,
            ['FedoraConnector\Api\Adapter\FedoraItemAdapter'],
            ['search', 'read']
            );
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec("CREATE TABLE fedora_item (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, job_id INT NOT NULL, uri VARCHAR(255) NOT NULL, last_modified DATETIME NOT NULL, UNIQUE INDEX UNIQ_D02FFFF9126F525E (item_id), INDEX IDX_D02FFFF9BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $connection->exec("CREATE TABLE fedora_import (id INT AUTO_INCREMENT NOT NULL, job_id INT NOT NULL, undo_job_id INT DEFAULT NULL, rerun_job_id INT DEFAULT NULL, added_count INT NOT NULL, updated_count INT NOT NULL, comment VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_EA775FC8BE04EA9 (job_id), UNIQUE INDEX UNIQ_EA775FC84C276F75 (undo_job_id), UNIQUE INDEX UNIQ_EA775FC87071F49C (rerun_job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $connection->exec("ALTER TABLE fedora_item ADD CONSTRAINT FK_D02FFFF9126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE;");
        $connection->exec("ALTER TABLE fedora_item ADD CONSTRAINT FK_D02FFFF9BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);");
        $connection->exec("ALTER TABLE fedora_import ADD CONSTRAINT FK_EA775FC8BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);");
        $connection->exec("ALTER TABLE fedora_import ADD CONSTRAINT FK_EA775FC84C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id);");
        $connection->exec("ALTER TABLE fedora_import ADD CONSTRAINT FK_EA775FC87071F49C FOREIGN KEY (rerun_job_id) REFERENCES job (id);");
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec("ALTER TABLE fedora_item DROP FOREIGN KEY FK_D02FFFF9126F525E;");
        $connection->exec("ALTER TABLE fedora_item DROP FOREIGN KEY FK_D02FFFF9BE04EA9;");
        $connection->exec("ALTER TABLE fedora_import DROP FOREIGN KEY FK_EA775FC8BE04EA9;");
        $connection->exec("ALTER TABLE fedora_import DROP FOREIGN KEY FK_EA775FC84C276F75;");
        $connection->exec("ALTER TABLE fedora_import DROP FOREIGN KEY FK_EA775FC87071F49C;");
        $connection->exec('DROP TABLE fedora_item');
        $connection->exec('DROP TABLE fedora_import');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        if (Comparator::lessThan($oldVersion, '1.6.0')) {
            $connection->exec("ALTER TABLE fedora_import ADD rerun_job_id INT DEFAULT NULL AFTER undo_job_id;");
            $connection->exec("ALTER TABLE fedora_import ADD CONSTRAINT FK_EA775FC8BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id);");
            $connection->exec("ALTER TABLE fedora_import ADD CONSTRAINT FK_EA775FC84C276F75 FOREIGN KEY (undo_job_id) REFERENCES job (id);");
            $connection->exec("ALTER TABLE fedora_import ADD CONSTRAINT FK_EA775FC87071F49C FOREIGN KEY (rerun_job_id) REFERENCES job (id);");
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.show.after',
            [$this, 'showSource']
            );

        $sharedEventManager->attach(
            \Omeka\Api\Adapter\ItemAdapter::class,
            'api.search.query',
            [$this, 'importSearch']
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
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $form = $formElementManager->get(ConfigForm::class);
        $html = $renderer->formCollection($form);
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
        if ($data['import_fedora'] == 1) {
            $fedoraImportData = [
                    'o:prefix' => 'fedora',
                    'o:namespace_uri' => 'http://fedora.info/definitions/v4/repository#',
                    'o:label' => 'Fedora Vocabulary', // @translate
                    'o:comment' => 'Vocabulary for a Fedora Repository', // @translate
                    'file' => OMEKA_PATH . '/modules/FedoraConnector/data/repository.rdf',
                    ];
            $response = $importer->import(
                'file', $fedoraImportData, ['file' => OMEKA_PATH . '/modules/FedoraConnector/data/repository.rdf']
            );
        }

        if ($data['import_ldp'] == 1) {
            $ldpImportData = [
                    'o:prefix' => 'ldp',
                    'o:namespace_uri' => 'http://www.w3.org/ns/ldp#',
                    'o:label' => 'Linked Data Platform Vocabulary', // @translate
                    'o:comment' => 'Vocabulary for a Linked Data Platform. Used by Fedora', // @translate
                    'file' => OMEKA_PATH . '/modules/FedoraConnector/data/repository.rdf',
                    ];
            $response = $importer->import(
                'file', $ldpImportData, ['file' => OMEKA_PATH . '/modules/FedoraConnector/data/ldp.rdf']
            );
        }
        //assuming that exceptions get thrown before here, just return true
        return true;
    }
    public function showSource($event)
    {
        $view = $event->getTarget();
        $item = $view->item;
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $response = $api->search('fedora_items', ['item_id' => $item->id()]);
        $fedoraItems = $response->getContent();
        if ($fedoraItems) {
            $fedoraItem = $fedoraItems[0];
            echo '<h3>' . $view->translate('Original') . '</h3>';
            echo '<p>' . $view->translate('Last Modified') . ' ' . $view->i18n()->dateFormat($fedoraItem->lastModified()) . '</p>';
            echo '<p><a href="' . $fedoraItem->uri() . '">' . $view->translate('Link') . '</a></p>';
        }
    }

    public function importSearch($event)
    {
        $query = $event->getParam('request')->getContent();
        if (isset($query['fedora_import_id'])) {
            $qb = $event->getParam('queryBuilder');
            $adapter = $event->getTarget();
            $importItemAlias = $adapter->createAlias();
            $qb->innerJoin(
                \FedoraConnector\Entity\FedoraItem::class, $importItemAlias,
                'WITH', "$importItemAlias.item = omeka_root.id"
            )->andWhere($qb->expr()->eq(
                "$importItemAlias.job",
                $adapter->createNamedParameter($qb, $query['fedora_import_id'])
            ));
        }
    }
}
