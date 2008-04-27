{assign var="metaDescription" value='INDEX_META_DESCRIPTION'|config}
{assign var="metaKeywords" value='INDEX_META_KEYWORDS'|config}

<div class="index">

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

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

</div>

{include file="layout/frontend/footer.tpl"}

</div>