{**
 * Smarty deal/landing view file.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2012 Satoshi Payne
 * @version    $Id: landing.tpl 105 2012-02-29 01:00:12Z Satoshi $
 * @category   Views
 * @package    Deal
 *}
{extends file='layouts/layout.tpl'}
{block name=content}
<div class="main">
	{if !$deals->isEmpty()}
		<div class="listingArea">
			<h2>Deals</h2>
			<div class="listingControls">
				<div class="summary">
					<span>Displaying {$deals->getFirstIndex()} to {$deals->getLastIndex()} of {$deals->getNbResults()} deals</span>
				</div>
				<div class="pagination clear">
					{$pagination->display()}
				</div>
				<div class="controls clear">
					<select name="SortBy">
						<option value="">Sort By</option>
						<option value="latest">Latest</option>
						<option value="dateenddesc">Ending Soon</option>
						<option value="pricedesc">Price</option>
						<option value="alphaasc">Name</option>
					</select>
				</div>
			</div>
			<div class="dealsListing clear">
			{foreach $deals as $i => $deal}
				{include file='partials/deal-item-large.tpl' deal=$deal}
			{/foreach}
			</div>
			<div class="listingControls">
				<div class="summary">
					<span>Displaying {$deals->getFirstIndex()} to {$deals->getLastIndex()} of {$deals->getNbResults()} deals</span>
				</div>
				<div class="pagination clear">
					{$pagination->display()}
				</div>
				<div class="controls clear">
					<select name="SortBy">
						<option value="">Sort By</option>
						<option value="latest">Latest</option>
						<option value="dateenddesc">Ending Soon</option>
						<option value="pricedesc">Price</option>
						<option value="alphaasc">Name</option>
					</select>
				</div>
			</div>
		</div>
	{else}
		<div class="listingArea">
			<div class="typography clear">
				<h2>No deals found</h2>
				<p>No deals were found in this section.</p>
			</div>
		</div>
	{/if}
</div>
{/block}