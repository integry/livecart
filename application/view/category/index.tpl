{pageTitle}{$category.pageTitle_lang|default:$category.name_lang}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	{include file="category/head.tpl"}

	{if $allFilters.filters}
		{include file="category/allFilters.tpl"}
	{/if}

	{if $foundCategories}
		{include file="category/foundCategories.tpl"}
	{/if}

	{if $categoryNarrow}
		{include file="category/narrowByCategory.tpl"}
	{elseif !$searchQuery && $subCategories}
		{include file="category/subcategoriesColumns.tpl"}
	{/if}

	{if $searchQuery && !$products}
		<p class="notFound">
			{t _not_found}
		</p>
	{/if}

	{if !$searchQuery && 1 == $currentPage}
		{block PRODUCT_LISTS}
	{/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>

		{if 'GRID' == $layout}
			{include file="category/productGrid.tpl" products=$subCatFeatured}
		{else}
			{include file="category/productList.tpl" products=$subCatFeatured}
		{/if}
	{/if}

	{include file="category/categoryProductList.tpl"}

</div>
{include file="layout/frontend/footer.tpl"}

</div>