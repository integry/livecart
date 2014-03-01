[[ global('layoutspanRight', 3) ]]

<div id="rightSide" class="col-sm-[[ global('layoutspanRight') ]] pull-right">
	right
	{#
	{block RIGHT_SIDE}
	{block MINI_CART}
	{block SALE_ITEMS}
	{block NEWEST_PRODUCTS}
	#}
	
</div>
