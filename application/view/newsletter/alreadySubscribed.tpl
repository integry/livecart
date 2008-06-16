{pageTitle}{t _already_subscribed}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/layout.tpl"}

<div id="content">
	<h1>{t _already_subscribed}</h1>

	<p>
		{t _already_subscribed_info}
	</p>
</div>
{include file="layout/frontend/footer.tpl"}

</div>