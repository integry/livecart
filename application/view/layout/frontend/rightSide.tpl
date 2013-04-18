{assign var="layoutspanRight" value=3 scope="global"}

<div id="rightSide" class="col-span-{$layoutspanRight}">
	<div id="contentWrapperRight"></div>
	{block RIGHT_SIDE}
	{block MINI_CART}
	{block SALE_ITEMS}
	{block NEWEST_PRODUCTS}
</div>
