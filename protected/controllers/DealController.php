<?php
/**
 * This file contains the DealController Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2012, Satoshi Payne
 */
/**
 * The DealController Controller Class defines a set of routes/actions for displaying deals on the site.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @version   $Id: DealController.php 98 2012-02-25 04:42:30Z Satoshi $
 * @category  Controllers
 * @package   Deal
 */
class DealController extends Controller
{
	/**
	 * Landing.
	 *
	 * Display a list of deals on the site.
	 */
	public function actionLanding()
	{
		// Params.
		
		$request = $this->request;
		$categoryUrl = $request->getQuery('c');
		$regionPath  = $request->getQuery('l');
		$keywords    = $request->getQuery('q');
		$pageNumber  = $request->getQuery('p');
		
		// Validation.
		
		$categoryUrl = Sanitise::validate_urltitle($categoryUrl, '');
		$regionPath  = Sanitise::validate_urltitle($regionPath, '');
		$keywords    = ($keywords != 'Search') ? $keywords : '';
		$pageNumber  = Sanitise::validate_int($pageNumber, true, 1);
		
		// Derived values.
		
		$pageSize = 12; // @todo: Don't use magic numbers.
		
		// Data.
		
		// Get deals.
		$q = DealQuery::getDealsQuery($categoryUrl, $regionPath, $keywords, $ordering);
		$deals      = $q->paginate($pageNumber, $pageSize);
		$dealsCount = $deals->getNbResults();
		
		// Pagination.
		$pagination = new PaginationControl();
		$pagination
			->setCurrentPage($pageNumber)
			->setPageSize($pageSize)
			->setTotalRecords($dealsCount)
			->setRenderFile('deal.tpl')
			->render();
		
		// Assign to View.
		$smarty = Yii::app()->viewRenderer->getSmarty();
		$smarty->assign('deals',      $deals);
		$smarty->assign('pagination', $pagination);
		$smarty->display('deal/landing.tpl');
	}
	
	/**
	 * Listing.
	 */
	public function actionListing()
	{
		
	}
	
	/**
	 * Deal.
	 */
	public function actionDeal()
	{
		
	}
}