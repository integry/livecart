{pageTitle}{$news.title_lang}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/layout.tpl"}

<div id="content">
	<h1>{$news.title_lang}</h1>
	<div class="newsDate">{$news.formatted_time.date_long}</div>

	<p>{$news.text_lang}</p>

	{if $news.moreText_lang}
		<p>{$news.moreText_lang}</p>
	{/if}

</div>
{include file="layout/frontend/footer.tpl"}

</div>