<?php
/**
 * This file contains the DataObject Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2011, Satoshi Payne
 */
use \Doctrine\Common\Collections, 
    \Doctrine\ORM\EntityRepository, 
    \Doctrine\ORM\Event as DoctrineEvent;

/**
 * The DataObject Entity Abstract Class contains properties and methods to add meta data and additional functionality
 * to entities.
 *
 * @author      Satoshi Payne <satoshi.payne@gmail.com>
 * @version     $Id: DataObject.php 63 2011-11-09 20:13:06Z satoshi $
 * @category    Models
 * @package     General
 * @subpackage  Metadata
 */
/** @MappedSuperclass */
abstract class DataObject
{
	// Fields.
	
	/** @Column(type="datetime") */
	protected $dateCreated;
	
	/** @Column(type="datetime") */
	protected $dateUpdated;
	
	/** @Column(type="datetime", nullable=true) */
	protected $dateDeleted;
	
	/** @Column(type="datetime", nullable=true) */
	protected $dateUndeleted;
	
	/** @Column(type="string", length=40, nullable=true) */
	protected $deleteContext;
	
	// Life-cycle Events.
	
	/** @PrePersist */
	public function actionOnPrePersist()
	{
		$this->dateCreated = new DateTime();
		$this->dateUpdated = new DateTime();
	}
	
	/** @PreUpdate */
	public function actionOnPreUpdate()
	{
		// Detect undeletion. Reset date deleted if date undeleted is more recent.
		if($this->dateDeleted !== null && $this->dateUndeleted !== null && $this->dateDeleted < $this->dateUndeleted) {
			$this->dateDeleted = null;
		}
		$this->dateUpdated = new DateTime();
	}
	
	/** @PreRemove */
	public function actionOnPreRemove()
	{
		// Don't perform delete on this object.
		$em = DoctrineComponent::getEntityManager();
		$em->detach($this);
		
		$this->dateDeleted = new DateTime();
		$this->softDelete();
	}
	
	// Field Accessors.
	
	public function getDateCreated() { return $this->dateCreated; }
	
	public function getDateUpdated() { return $this->dateUpdated; }
	
	public function getDateDeleted() { return $this->dateDeleted; }
	
	public function getDateUndeleted() { return $this->dateUndeleted; }
	
	public function getDeleteContext() { return $this->deleteContext; }
	public function setDeleteContext($value) { $this->deleteContext = $value; return $this; }
	
	// Methods.
	
	protected function softDelete()
	{
		$em = DoctrineComponent::getEntityManager();
		
		$currTime = new DateTime;
		$c = get_class($this);
		
		$params = array(
			'currTime' => $currTime, 
			'id' => $this->getId()
		);
		
		$sql = '
			UPDATE ' . $c . ' e 
			SET e.dateDeleted = :currTime
			WHERE e.dateDeleted IS NULL
			  AND e.id = :id';
		$query = $em->createQuery($sql);
		$query->setParameters($params);
		$query->execute();
	}
}