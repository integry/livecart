{pageTitle}{'STORE_HEADLINE'|config}{/pageTitle}
{assign var="metaDescription" value='INDEX_META_DESCRIPTION'|config}
{assign var="metaKeywords" value='INDEX_META_KEYWORDS'|config}

<div class="index">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	{block HOME-PAGE-TOP}

	{if 'HOME_PAGE_SUBCATS'|config}
		{include file="category/subcategoriesColumns.tpl"}
	{/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>
		{include file="category/productListLayout.tpl" layout='FEATURED_LAYOUT'|config|default:$layout products=$subCatFeatured}
	{/if}

	{if $news}
		{include file="index/latestNews.tpl"}
	{/if}

	{include file="category/categoryProductList.tpl"}

</div>

{include file="layout/frontend/footer.tpl"}

</div>