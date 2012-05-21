<?php
/**
 * This file contains the IViewable Interface.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2011, Satoshi Payne
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
	const CONTEXT_DEFAULT = 0;
	
	public function getViewableLink($options, $context);
}