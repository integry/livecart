<div id="helpNav">
{foreach from=$breadCrumb item=title key=key name=breadCrumb}
	{if $smarty.foreach.breadCrumb.last}
		<span id="breadCrumbLast">{$title}</span>
	{else}
		<a href="{link controller=backend.help action=view id=$key}">{$title}</a> :
	{/if}
{/foreach}
</div>

<div id="helpContent">
	{include file=$helpTemplate}
</div>