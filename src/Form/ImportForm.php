<?php
namespace FedoraConnector\Form;

use Omeka\Form\Element\ItemSetSelect;
use Omeka\Form\Element\SiteSelect;
use Omeka\Settings\UserSettings;
use Omeka\Api\Manager as ApiManager;
use Laminas\Authentication\AuthenticationService;
use Laminas\Form\Form;

class ImportForm extends Form
{
    /**
     * @var UserSettings
     */
    protected $userSettings;

    /**
     * @var AuthenticationService
     */
    protected $AuthenticationService;

    /**
     * @var ApiManager
     */
    protected $apiManager;

    public function init()
    {
        $this->add([
            'name' => 'container_uri',
            'type' => 'url',
            'options' => [
                'label' => 'Fedora container URI', // @translate
                'info' => 'The URI of the Fedora container for the repository, collection or resource to import.', // @translate
            ],
            'attributes' => [
                'id' => 'container_uri',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'ingest_files',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Import files into Omeka S', // @translate
                'info' => 'If checked, original files will be imported into Omeka S. Otherwise, derivates will be displayed when possible, with links back to the original file in the Fedora repository.', // @translate
            ],
            'attributes' => [
                'id' => 'ingest-files',
            ],
        ]);

        $this->add([
            'name' => 'ignore_parent',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Ignore parent container', // @translate
                'info' => 'If checked, only descendents of the container at URI above will be imported as items--not the parent container itself.', // @translate
            ],
            'attributes' => [
                'id' => 'ignore-parent',
            ],
        ]);

        $this->add([
            'name' => 'only_direct_children',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Import direct descendents only', // @translate
                'info' => 'If checked, only direct descendents of the container at URI above will be imported (i.e. no children of children). Otherwise, all resources below container will be recursively imported', // @translate
            ],
            'attributes' => [
                'id' => 'only_direct_children',
            ],
        ]);

        $this->add([
            'name' => 'comment',
            'type' => 'textarea',
            'options' => [
                'label' => 'Comment', // @translate
                'info' => 'A note about the purpose or source of this import', // @translate
            ],
            'attributes' => [
                'id' => 'comment',
            ],
        ]);

        $this->add([
            'name' => 'itemSets',
            'type' => ItemSetSelect::class,
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select item set(s)', // @translate
                'multiple' => true,
                'id' => 'item-sets',
            ],
            'options' => [
                'label' => 'Item sets', // @translate
                'info' => 'Optional. Import items into item set(s).', // @translate
                'empty_option' => ''
            ],
        ]);

        // Merge assign_new_item sites and default user sites
        $defaultAddSiteRepresentations = $this->getApiManager()->search('sites', ['assign_new_items' => true])->getContent();
        foreach ($defaultAddSiteRepresentations as $defaultAddSiteRepresentation) {
            $defaultAddSites[] = $defaultAddSiteRepresentation->id();
        }
        $defaultAddSiteStrings = $defaultAddSites ?? [];

        $userId = $this->getAuthenticationService()->getIdentity()->getId();
        $userDefaultSites = $userId ? $this->getUserSettings()->get('default_item_sites', null, $userId) : [];
        $userDefaultSiteStrings = $userDefaultSites ?? [];

        $sites = array_merge($defaultAddSiteStrings, $userDefaultSiteStrings);

        $this->add([
            'name' => 'itemSites',
            'type' => SiteSelect::class,
            'attributes' => [
                'value' => $sites,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select site(s)', // @translate
                'multiple' => true,
                'id' => 'item-sites',
            ],
            'options' => [
                'label' => 'Sites', // @translate
                'info' => 'Optional. Import items into site(s).', // @translate
                'empty_option' => '',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'itemSets',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'itemSites',
            'required' => false,
        ]);
    }

    /**
     * @param UserSettings $userSettings
     */
    public function setUserSettings(UserSettings $userSettings)
    {
        $this->userSettings = $userSettings;
    }

    /**
     * @return UserSettings
     */
    public function getUserSettings()
    {
        return $this->userSettings;
    }

    /**
     * @param AuthenticationService $AuthenticationService
     */
    public function setAuthenticationService(AuthenticationService $AuthenticationService)
    {
        $this->AuthenticationService = $AuthenticationService;
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->AuthenticationService;
    }

    /**
     * @param ApiManager $apiManager
     */
    public function setApiManager(ApiManager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    /**
     * @return ApiManager
     */
    public function getApiManager()
    {
        return $this->apiManager;
    }
}
