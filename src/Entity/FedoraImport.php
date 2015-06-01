<?php
namespace FedoraConnector\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Job;

class FedoraImport extends AbstractEntity{
    
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @OneToOne(targetEntity="Omeka\Entity\Job")
     * @JoinColumn(nullable=false)
     */
    protected $job;
    
    /**
     * @Column(type="integer")
     */
    protected $resource_count;
    
    /**
     * @OneToOne(targetEntity="Omeka\Entity\Job")
     * @JoinColumn(nullable=true)
     */
    protected $undo_job;
    
    /**
     * @Column(type="string")
     * @var unknown_type
     */
    protected $comment;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setJob(Job $job)
    {
        $this->job = $job;
    }

    public function getJob()
    {
        return $this->job;
    }
    
    public function setUndoJob(Job $job)
    {
        $this->job = $job;
    }

    public function getUndoJob()
    {
        return $this->job;
    }
    
    public function setResourceCount(int $count)
    {
        $this->resource_count = $count;
    }
    
    public function getResourceCount()
    {
        return $this->resource_count;
    }
}