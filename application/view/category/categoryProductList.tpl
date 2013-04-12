{if $products}
	<div class="resultStats">
		<div class="pagingInfo">
			{maketext text=_showing_products params="`$offsetStart`,`$offsetEnd`,`$count`"}
		</div>

		{include file="category/block/switchListLayout.tpl"}

		<div class="sortOptions">
			{if $sortOptions && ($sortOptions|@count > 1)}
				{t _sort_by}
				{form handle=$sortForm action="self" method="get"}
				{selectfield id="productSort" name="sort" options=$sortOptions onchange="this.form.submit();"}
				{/form}
			{/if}
			&nbsp;
		</div>
	</div>

	{if $products}
		<form action="{link controller=category action=listAction returnPath=true}" method="post">
			{include file="category/productListLayout.tpl" products=$products}
		</form>
	{/if}

	{if $count > $perPage}
		{paginate current=$currentPage count=$count perPage=$perPage url=$url}
	{/if}
{/if}
