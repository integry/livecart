<h2>{t _latest_news}</h2>
{foreach from=$news item=newsItem name="news"}
	{% if !$smarty.foreach.news.last || !$isNewsArchive %}
		[[ partial('news/newsEntry.tpl', ['entry': newsItem]) ]]
	{% else %}
	<div class="newsArchive">
		<a href="{link controller=news}">{t _news_archive}</a>
	</div>
	{% endif %}
{/foreach}
