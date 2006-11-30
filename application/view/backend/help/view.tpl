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

	<div id="helpRelated">
		Related topics
	</div>
</div>

<div id="helpFooter">
	<div id="helpFooterContent">
		{if '' != $prevId}
			<a href="{help $prevId}">&lt; {$prevTitle}</a>
			{if '' != $nextId}
			:
			{/if}
		{/if}
		{if '' != $nextId}
			<a href="{help $nextId}">{$nextTitle} &gt; </a>
		{/if}
	</div>
</div>

{include file=layout/help/footer.tpl}