<?php
/**
 * This file contains the IViewable Interface.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2012, Satoshi Payne
 */
/**
 * The IViewable Interface defines the interface for objects that are viewable through a link.
 *
 * @author      Satoshi Payne <satoshi.payne@gmail.com>
 * @version     $Id: IViewable.php 98 2012-02-25 04:42:30Z Satoshi $
 * @category    Models
 * @package     Logging
 * @subpackage  Interfaces
 */
interface IViewable
{
	// Static properties.
	
	// Link contexts.
	
	/**
	 * var CONTEXT_DEFAULT The link context to display the "default" page.
	 */
	const CONTEXT_DEFAULT = 0;
	
	/**
	 * Derive the link to the current object based on options and context.
	 *
	 * @param mixed $options The options array to assist link generation.
	 * @param const $context The context to base the link generation on.
	 * @return string        The link to the current object.
	 */
	public function getViewableLink($options, $context);
}