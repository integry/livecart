{pageTitle}{t _confirming_email}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{t _confirming_email}</h1>

	<p>
	{if $subscriber.isEnabled}
		{t _confirm_successful}
	{else}
		{t _confirm_unsuccessful}
	{/if}
	</p>
</div>
{include file="layout/frontend/footer.tpl"}

</div>