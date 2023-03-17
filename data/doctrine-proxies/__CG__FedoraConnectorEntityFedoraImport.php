<?php

namespace DoctrineProxies\__CG__\FedoraConnector\Entity;


/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class FedoraImport extends \FedoraConnector\Entity\FedoraImport implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array<string, null> properties to be lazy loaded, indexed by property name
     */
    public static $lazyPropertiesNames = array (
);

    /**
     * @var array<string, mixed> default values of properties to be lazy loaded, with keys being the property names
     *
     * @see \Doctrine\Common\Proxy\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array (
);



    public function __construct(?\Closure $initializer = null, ?\Closure $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', 'id', 'job', 'addedCount', 'updatedCount', 'undoJob', 'rerunJob', 'comment'];
        }

        return ['__isInitialized__', 'id', 'job', 'addedCount', 'updatedCount', 'undoJob', 'rerunJob', 'comment'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (FedoraImport $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy::$lazyPropertiesDefaults as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load(): void
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized(): bool
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized): void
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null): void
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer(): ?\Closure
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null): void
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner(): ?\Closure
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @deprecated no longer in use - generated code now relies on internal components rather than generated public API
     * @static
     */
    public function __getLazyProperties(): array
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setJob(\Omeka\Entity\Job $job)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setJob', [$job]);

        return parent::setJob($job);
    }

    /**
     * {@inheritDoc}
     */
    public function getJob()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getJob', []);

        return parent::getJob();
    }

    /**
     * {@inheritDoc}
     */
    public function setUndoJob(\Omeka\Entity\Job $job)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUndoJob', [$job]);

        return parent::setUndoJob($job);
    }

    /**
     * {@inheritDoc}
     */
    public function getUndoJob()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUndoJob', []);

        return parent::getUndoJob();
    }

    /**
     * {@inheritDoc}
     */
    public function setRerunJob(\Omeka\Entity\Job $job)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRerunJob', [$job]);

        return parent::setRerunJob($job);
    }

    /**
     * {@inheritDoc}
     */
    public function getRerunJob()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRerunJob', []);

        return parent::getRerunJob();
    }

    /**
     * {@inheritDoc}
     */
    public function setAddedCount($count)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAddedCount', [$count]);

        return parent::setAddedCount($count);
    }

    /**
     * {@inheritDoc}
     */
    public function getAddedCount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAddedCount', []);

        return parent::getAddedCount();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedCount($count)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedCount', [$count]);

        return parent::setUpdatedCount($count);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedCount()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedCount', []);

        return parent::getUpdatedCount();
    }

    /**
     * {@inheritDoc}
     */
    public function setComment($comment)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setComment', [$comment]);

        return parent::setComment($comment);
    }

    /**
     * {@inheritDoc}
     */
    public function getComment()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getComment', []);

        return parent::getComment();
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getResourceId', []);

        return parent::getResourceId();
    }

}
