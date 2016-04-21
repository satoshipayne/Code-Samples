<?php
/**
 * This file contains the Deal Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2012, Satoshi Payne
 */
/**
 * Skeleton subclass for representing a row from the 'deal' table.
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @version   $Id: Deal.php 1 2012-05-21 04:05:56Z Satoshi $
 * @category  Models
 * @package   propel.generator.Build
 */
class Deal extends BaseDeal implements IViewable
{
	// Static properties.
	
	// Link contexts.
	
	/**
	 * var LINK_CHECKOUT The link context to display a link to the deal's checkout page.
	 */
	const LINK_CHECKOUT = 1;
	
	// Field Accessors.
	
	/**
	 * Set the value of [title] column.
	 * 
	 * @param  string $value The new value.
	 * @return Deal          The current object (for fluent API support).
	 */
	public function setTitle($value)
	{
		$this->title = $value;
		
		// URL Title.
		if(!$this->urltitle) { // TODO: Refactor into common method.
			$urlTitle = trim($value);
			$urlTitle = str_replace('&', 'and', $urlTitle);
			$urlTitle = str_replace(' ', '-', $urlTitle);
			$urlTitle = preg_replace('/[^a-z0-9\\-]+/i', '', $urlTitle);
			$urlTitle = strtolower($urlTitle);
			$this->urltitle = $urlTitle;
			
			// Mark fields to be persisted.
			$this->modifiedColumns[] = DealPeer::URLTITLE;
		}
		
		// Mark fields to be persisted.
		$this->modifiedColumns[] = DealPeer::TITLE;
		
		return $this;
	}
	
	/**
	 * Derive the image path of the main thumbnail for this deal.
	 * 
	 * @return string The full path of the image file.
	 */
	public function getImagePath()
	{
		return ''; // Incomplete.
	}
	
	// Association Accessors.
	
	/**
	 * Wrapper method for the generated 'addCategory' method. Add a category to the current object.
	 *
	 * @param  DealCategory $l The category object to add.
	 * @return Deal            The current object (for fluent API support).
	 */
	public function addCategoryToCollection(DealCategory $l)
	{
		$mm = new DealsDealCategories;
		$mm->setCategory($l);
		return $this->addCategory($mm);
	}
	
	// Methods.
	
	// IViewable Methods.
	
	/**
	 * Derive the link to the current object based on options and context.
	 *
	 * @param  mixed $options The options array to assist link generation.
	 * @param  const $context The context to base the link generation on.
	 * @return string         The link to the current object.
	 */
	public function getViewableLink($options = null, $context = IViewable::CONTEXT_DEFAULT)
	{
		$link = '';
		switch($context)
		{
			case IViewable::CONTEXT_DEFAULT:
				$link = '/deal/' . $this->getUrlTitle() . '-' . $this->getId();
				break;
			case self::LINK_CHECKOUT:
				$link = '/checkout/' . $this->getUrlTitle() . '-' . $this->getId();
				break;
			default:
				throw new Exception('Link failed to be created for deal.');
		}
		return $link;
	}
	
	// Hooks.
	
	/**
	 * Actions to perform on the current object before inserting.
	 */
	function preInsert()
	{
		$this
			->setDateCreated(new DateTime)
			->setDateUpdated(new DateTime);
		return true;
	}
	
	/**
	 * Actions to perform on the current object before updating.
	 */
	function preUpdate()
	{
		// Detect undeletion. Reset date deleted if date undeleted is more recent.
		if($this->getDateDeleted !== null && $this->getDateUndeleted !== null && $this->getDateDeleted < $this->getDateUndeleted) {
			$this->setDateDeleted(null);
		}
		$this->setDateUpdated(new DateTime);
		return true;
	}
	
	/**
	 * Actions to perform on the current object before deleting.
	 */
	public function preDelete()
	{
		// Don't perform delete on this object.
		$this
			->setDateDeleted(new DateTime)
			->save();
		return false;
	}
} // Deal