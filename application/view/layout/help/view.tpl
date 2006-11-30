{php}
	if (isset($GLOBALS['PAGE_TITLE']))
	{
	  	$this->assign('PAGE_TITLE', $GLOBALS['PAGE_TITLE']);
	}
{/php}

{include file=layout/help/header.tpl}

<div id="helpNav">
{foreach from=$breadCrumb item=title key=key name=breadCrumb}
	{if !$smarty.foreach.breadCrumb.last}
		<a href="{link controller=backend.help action=view id=$key}">{$title}</a> &gt;
	{else}
		{if '' != $PAGE_TITLE}
			<a href="{link controller=backend.help action=view id=$key}">{$title}</a> &gt;
			<span id="breadCrumbLast">{$PAGE_TITLE}</span>
		{else}
			<span id="breadCrumbLast">{$title}</span>
		{/if}
	{/if}
{/foreach}
</div>

{if '' != $PAGE_TITLE}
	<h1>{$PAGE_TITLE}</h1>
{/if}

{$ACTION_VIEW}

{include file=layout/help/footer.tpl}