{pageTitle}{$news.title_lang}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	<div class="newsDate">{$news.formatted_time.date_long}</div>

	<div class="newsEntry">
		<p>{$news.text_lang}</p>

		{if $news.moreText_lang}
			<p>{$news.moreText_lang}</p>
		{/if}
	</div>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}