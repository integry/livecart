<div id="content" class="col col-lg-{12-$layoutspanLeft-$layoutspanRight}">

{block BREADCRUMB}

{assign var=title value=$title|default:$PAGE_TITLE}
{if $title && !$hideTitle}
	<h1>{translate text=$title}</h1>
{/if}

{include file="block/message.tpl"}
