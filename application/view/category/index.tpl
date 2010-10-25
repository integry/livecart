{pageTitle}{$category.pageTitle_lang|default:$category.name_lang}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang|@strip_tags}
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

	{if $modelSearch}
		{include file="search/block/allResults.tpl"}
	{/if}

	{if $categoryNarrow}
		{include file="category/narrowByCategory.tpl"}
	{elseif !$searchQuery && $subCategories && !'HIDE_SUBCATS'|config}
		{include file="category/subcategoriesColumns.tpl"}
	{/if}

	{if $searchQuery && !$products}
		<p class="notFound">
			{t _not_found}
		</p>
	{/if}

	{if $appliedFilters && !$products}
		<p class="notFound">
			<span class='notFoundMain'>{t _no_products}</span>
		</p>
	{/if}

	{if !$searchQuery && 1 == $currentPage}
		{block PRODUCT_LISTS}
	{/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>

		{include file="category/productListLayout.tpl" layout='FEATURED_LAYOUT'|config|default:$layout products=$subCatFeatured}
	{/if}

	{block FILTER_TOP}

	{include file="category/categoryProductList.tpl"}

</div>
{include file="layout/frontend/footer.tpl"}

</div>