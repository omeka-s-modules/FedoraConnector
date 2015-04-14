<?php
namespace FedoraConnector;

use Omeka\Module\AbstractModule;
use Omeka\Model\Entity\Job;
use Zend\ServiceManager\ServiceLocatorInterface;

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
}