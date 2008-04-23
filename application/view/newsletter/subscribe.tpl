{pageTitle}{t _confirm_subscription}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{t _confirm_subscription}</h1>

	<p>
		{t _confirm_instructions}
	</p>
</div>
{include file="layout/frontend/footer.tpl"}

</div>