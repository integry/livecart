<div class="box latestNews">
	<div class="title"><div>{t _latest_news}</div></div>

	<div class="content">
		<ul class="latestNewsSide">
			{foreach from=$news item=entry name=news}
				{% if !$smarty.foreach.news.last || !$isNewsArchive %}
					<li>
						<a href="{newsUrl news=$entry}">[[entry.title()]]</a>
						<span class="date">[[entry.formatted_time.date_medium]]</span>
					</li>
				{% else %}
					<div class="newsArchive">
						<a href="[[ url("news") ]]">{t _news_archive}</a>
					</div>
				{% endif %}
			{/foreach}
		</ul>
	</div>
</div>