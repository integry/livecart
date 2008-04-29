{pageTitle}{$page.title_lang}{/pageTitle}

<div class="staticPageView staticPage_{$page.ID}">

{include file="layout/frontend/layout.tpl"}

<div id="content">
	<h1>{$page.title_lang}</h1>
	<div class="staticPageText">
		{$page.text_lang}
	</div>
</div>

{include file="layout/frontend/footer.tpl"}

</div>