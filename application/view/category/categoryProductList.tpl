{if $products}
	<div class="row resultStats">
		<div class="col col-lg-6 pagingInfo text-muted">
			{maketext text=_showing_products params="`$offsetStart`,`$offsetEnd`,`$count`"}
		</div>

		<div class="col col-lg-6 listOptions">
			{if $sortOptions && ($sortOptions|@count > 1)}
			<span class="sortOptions">
					{t _sort_by}
					{form handle=$sortForm action="self" method="get"}
					{selectfield id="productSort" name="sort" options=$sortOptions onchange="this.form.submit();"}
					{/form}
			</span>
			{/if}

			{include file="category/block/switchListLayout.tpl"}
		</div>
	</div>

	<hr />

	{if $products}
		<form action="{link controller=category action=listAction returnPath=true}" method="post" class="form-horizontal">
			{include file="category/productListLayout.tpl" products=$products}
		</form>
	{/if}

	{if $count > $perPage}
		{paginate current=$currentPage count=$count perPage=$perPage url=$url}
	{/if}
{/if}
