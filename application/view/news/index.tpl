{pageTitle}{t _news}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	<ul class="news">
		{foreach from=$news item=entry}
			<li class="newsEntry">
				{include file="news/newsEntry.tpl" entry=$entry}
			</li>
		{/foreach}
	</ul>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}