{assign 'linkItem'     $deal->getViewableLink()}
{assign 'linkCheckout' $deal->getViewableLink($options, Deal::LINK_CHECKOUT)}
<div class="dealItem 
	{if $i % 4 == 0}first-child {/if}
	{if $i % 4 == 3}last-child {/if}
">
	<h3 class="title"><a href="{$siteUrl}{$linkItem|escape}">{$deal->getTitle()|escape}</a></h3>
	{if $deal->getImagePath()}
		<div class="imageHolder">
			<a href="{$siteUrl}{$linkItem|escape}"><img src="{$deal->getImagePath()|escape}" alt="{$deal->getTitle()|escape}" /></a>
		</div>
	{/if}
	<p>{$deal->getSummary()|escape}</p>
	<a class="readMore" href="{$siteUrl}{$linkItem|escape}">Read more</a>
	<div class="actions">
		<span class="price">${$deal->getFinalPrice()}</span>
		<span class="discount">({$deal->getDiscountPercent()}% off!)</span>
		<a class="action-checkout" href="{$secureUrl}{$linkCheckout|escape}">Buy this item</a>
	</div>
</div>