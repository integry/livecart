<div id="content" class="span9">

{assign var=title value=$title|default:$PAGE_TITLE}
{if $title && !$hideTitle}
	<h1>{translate text=$title}</h1>
{/if}

{include file="block/message.tpl"}