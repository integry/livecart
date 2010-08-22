{pageTitle}{$page.title_lang}{/pageTitle}
{assign var="metaDescription" value=$page.metaDescription_lang|@strip_tags}

<div class="staticPageView staticPage_{$page.ID}">

{include file="layout/frontend/layout.tpl"}

<div id="content">
	<h1>{$page.title_lang}</h1>
	{if $subPages}
		<div class="staticSubpages">
			<h2>{t _subpages}</h2>
			<ul>
				{foreach from=$subPages item=subPage}
					<li id="static_{$subPage.ID}"><a href="{pageUrl data=$subPage}">{$subPage.title_lang}</a></li>
				{/foreach}
			</ul>
		</div>
	{/if}
	<div class="staticPageText">
		{$page.text_lang}
	</div>
</div>

{include file="layout/frontend/footer.tpl"}

</div>