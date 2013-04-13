<div id="content" class="col-span-9">

{block BREADCRUMB}

{assign var=title value=$title|default:$PAGE_TITLE}
{if $title && !$hideTitle}
	<h1>{translate text=$title}</h1>
{/if}

{include file="block/message.tpl"}
