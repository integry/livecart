<div id="mainInfo">
	{$presentation|@var_Dump}
	{if $presentation.isAllVariations}
		{block PRODUCT-PURCHASE-VARIATIONS}
	{else}
		{blocks id="PRODUCT-MAININFO-CONTAINER" blocks="
				PRODUCT-PURCHASE
				PRODUCT-OVERVIEW"}
	{/if}
</div>
