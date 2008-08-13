{pageTitle}{'STORE_NAME'|config}{/pageTitle}
{assign var="metaDescription" value='INDEX_META_DESCRIPTION'|config}
{assign var="metaKeywords" value='INDEX_META_KEYWORDS'|config}

<div class="index">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	{include file="category/subcategoriesColumns.tpl"}

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