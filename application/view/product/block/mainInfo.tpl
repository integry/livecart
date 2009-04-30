<div id="mainInfo">
	{if $presentation.isAllVariations && $variations}
		{block PRODUCT-PURCHASE-VARIATIONS}
	{else}
		{blocks id="PRODUCT-MAININFO-CONTAINER" blocks="
				PRODUCT-PURCHASE
				PRODUCT-OVERVIEW"}
	{/if}
</div>
