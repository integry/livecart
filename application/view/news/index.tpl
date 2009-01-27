{pageTitle}{t _news}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

{include file="layout/frontend/layout.tpl"}

<div id="content">
	<h1>{t _news}</h1>

	<ul class="news">
		{foreach from=$news item=entry}
			<li class="newsEntry">
				{include file="news/newsEntry.tpl" entry=$entry}
			</li>
		{/foreach}
	</ul>

</div>

{include file="layout/frontend/footer.tpl"}