{pageTitle}{'STORE_HEADLINE'|config}{/pageTitle}
{assign var="metaDescription" value='INDEX_META_DESCRIPTION'|config}
{assign var="metaKeywords" value='INDEX_META_KEYWORDS'|config}

{include file="layout/frontend/layout.tpl"}

{include file="block/content-start.tpl"}

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

{include file="block/content-stop.tpl"}

{include file="layout/frontend/footer.tpl"}