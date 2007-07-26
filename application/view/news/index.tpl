{pageTitle}{$category.name_lang}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{t _news}</h1>

	<ul class="news">
		{foreach from=$news item=entry}
			<li>
				<fieldset class="container">
					<h2 style="float: left;">{$entry.title_lang}</h2>
					<span style="float: right;">{$entry.formatted_time.date_medium}</span>
				</fieldset>

				{$entry.text_lang}
			</li>
		{/foreach}	
	</ul>

</div>		
{include file="layout/frontend/footer.tpl"}

</div>