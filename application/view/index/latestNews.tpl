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
