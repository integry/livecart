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

	{if $news}
		<h2>{t _latest_news}</h2>
		<ul class="news">
		{foreach from=$news item=newsItem name="news"}
			{if !$smarty.foreach.news.last || !$isNewsArchive}
				<li class="newsEntry">
					{include file="news/newsEntry.tpl" entry=$newsItem}
				</li>
			{else}
				<div class="newsArchive">
					<a href="{link controller=news}">{t _news_archive}</a>
				</div>
			{/if}
		{/foreach}
		</ul>
	{/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>
		{include file="category/productListLayout.tpl" layout='FEATURED_LAYOUT'|config|default:$layout products=$subCatFeatured}
	{/if}

	{include file="category/categoryProductList.tpl"}

</div>

{include file="layout/frontend/footer.tpl"}

</div>