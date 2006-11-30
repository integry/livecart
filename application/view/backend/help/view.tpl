{include file=layout/help/header.tpl}

<div id="helpNav">
{foreach from=$breadCrumb item=title key=key name=breadCrumb}
	{if !$smarty.foreach.breadCrumb.last}
		<a href="{link controller=backend.help action=view id=$key}">{$title}</a> &gt;
	{else}
		<span id="breadCrumbLast">{$title}</span>
	{/if}
{/foreach}
</div>

{if '' != $PAGE_TITLE}
	<h1>{$PAGE_TITLE}</h1>
{/if}

<div id="helpContent">
	{include file=$helpTemplate}
</div>

{include file=layout/help/footer.tpl}