{pageTitle}{t _news}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

{foreach from=$news item=entry}
	{include file="news/newsEntry.tpl" entry=$entry}
{/foreach}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}