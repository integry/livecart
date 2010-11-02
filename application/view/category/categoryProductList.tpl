{if $products}
	<div class="resultStats">
		<fieldset class="container">
		<div class="pagingInfo">
			{maketext text=_showing_products params=$offsetStart,$offsetEnd,$count}
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

		</fieldset>
	</div>

	{if $products}
		<form action="{link controller=category action=listAction returnPath=true}" method="post">
			{include file="category/productListLayout.tpl" products=$products}
		</form>
	{/if}

	{if $count > $perPage}
		<div class="resultPages">
			<span>{t _pages}:</span> {paginate current=$currentPage count=$count perPage=$perPage url=$url}
		</div>
	{/if}
{/if}