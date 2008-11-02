{if $products}
	<fieldset class="container">
	<div class="resultStats">
		<div class="pagingInfo">
			{maketext text=_showing_products params=$offsetStart,$offsetEnd,$count}
		</div>

		{if 'ALLOW_SWITCH_LAYOUT'|@config}
			<div class="categoryLayoutSwitch">
				{if 'GRID' == $layout}
					<a class="layoutSetList" href="{$layoutUrl}">{t _view_as_list}</a>
				{else}
					<a class="layoutSetGrid" href="{$layoutUrl}">{t _view_as_grid}</a>
				{/if}
			</div>
		{/if}

		<div class="sortOptions">
			{if $sortOptions && ($sortOptions|@count > 1)}
				{t _sort_by}
				{form handle=$sortForm action="self" method="get"}
				{selectfield id="productSort" name="sort" options=$sortOptions onchange="this.form.submit();"}
				{/form}
			{/if}
			&nbsp;
		</div>
		<div class="clear"></div>
	</div>
	</fieldset>

	{if 'GRID' == $layout}
		{include file="category/productGrid.tpl" products=$products}
	{else}
		{include file="category/productList.tpl" products=$products}
	{/if}

	{if $count > $perPage}
		<div class="resultPages">
			<span>{t _pages}:</span> {paginate current=$currentPage count=$count perPage=$perPage url=$url}
		</div>
	{/if}
{/if}