<?php
/**
 * Skeleton subclass for performing query and update operations on the 'deal' table.
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package propel.generator.Build
 */
class DealQuery extends BaseDealQuery
{
	// Constants.
	
	/**
	 * var TABLE_NAME The table name which is to be used as a default alias for queries.
	 */
	const TABLE_NAME = 'Deal';
	
	// Stored queries.
	
	/**
	 * Get the query object for retrieving a set of deals.
	 *
	 * @param string $categoryUrl The urltitle of the category filter.
	 * @param string $regionPath  The urltitle of the region path filter, this is based on the region's materialised path.
	 * @param string $keywords    The keywords to search.
	 * @param string $ordering    The ordering key to use to order the results.
	 * @return DealQuery          The query encapsulating the query to execute to return the set of deals.
	 */
	public function getDealsQuery($categoryUrl, $regionPath, $keywords, $ordering)
	{
		$alias = self::TABLE_NAME;
		
		$query = DealQuery::create()
			->getFinalPrice()
		
			// Category filter.
			 ->_if(strlen($categoryUrl))
				->useCategoryQuery()
					->filterByDealId($alias . '.id') // Needed to include many-to-many association.
					->useCategoryQuery()
						->filterByUrlTitle($categoryUrl)
					->endUse()
				->endUse()
			->_endif()
			
			// Region filter.
			->_if(strlen($regionPath))
				->useRegionQuery()
					->filterByUrlPath($regionPath . '%')
				->endUse()
			->_endif()
			
			// Search filter.
			->_if(strlen($keywords))
				->filterBySearchKeyword($keywords)
			->_endif()
			
			// Ordering.
			->_if($ordering == '' || $ordering == 'latest')
				->orderBy($alias . '.dateStart', 'DESC')
			->_elseif($ordering == 'dateenddesc')
				->orderBy($alias . '.dateFinish', 'ASC')
			->_elseif($ordering == 'pricedesc')
				->orderBy($alias . '.finalPrice', 'DESC')
			->_elseif($ordering == 'alphaasc')
				->orderBy($alias . '.title', 'ASC')
			->_endif()
			
			->isActive();
		return $query;
	}
	
	// Query parts.
	
	/**
	 * Append the 'finalPrice' column to the set of deal results.
	 *
	 * @param string $alias The query alias to use.
	 * @return DealQuery    The current query object.
	 */
	public function getFinalPrice($alias = self::TABLE_NAME)
	{
		return $this
			->withColumn($alias . '.price * (1 - (' . $alias . '.discountPercent / 100))', 'finalPrice');
	}
	
	/**
	 * Append the conditions to determining if each record is "active".
	 *
	 * @param string $alias The query alias to use.
	 * @return DealQuery    The current query object.
	 */
	public function isActive($alias = self::TABLE_NAME)
	{
		$time = date('Y-m-d h:i:s');
		
		return $this
			->where($alias . '.active = 1')
			->where($alias . '.dateStart <= ?', $time)
			->where($alias . '.dateFinish >= ?', $time)
			->where($alias . '.dateDeleted IS NULL');
	}
	
	/**
	 * Append the conditions to filter results based on keyword search.
	 *
	 * @param string $keywords The keywords to search.
	 * @param string $alias    The query alias to use.
	 * @return DealQuery       The current query object.
	 */
	public function filterBySearchKeyword($keywords, $alias = self::TABLE_NAME)
	{
		// @todo: Use Lucene Search or another search plugin later.
		return $this
			->condition('searchtitle', $alias . '.title LIKE ?', '%' . $keywords . '%')
			->condition('searchsummary', $alias . '.summary LIKE ?', '%' . $keywords . '%')
			->where(array('searchtitle', 'searchsummary'), 'or');
	}
} // DealQuery