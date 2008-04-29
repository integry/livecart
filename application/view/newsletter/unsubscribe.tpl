{pageTitle}{t _unsubscribe}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/layout.tpl"}

<div id="content">
	<h1>{t _unsubscribe}</h1>

	<p>
		{t _unsubscribe_sucessful}
	</p>
</div>
{include file="layout/frontend/footer.tpl"}

</div>