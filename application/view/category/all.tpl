{pageTitle}{t _all_categories}{/pageTitle}
{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	{foreach from=$sorted item=categories key=letter}
		<h2>{$letter|@capitalize}</h2>

		{foreach from=$categories item=category}
			{if !$index || (($totalCount/2) <= $index && $columns < 2)}
				{if $columns}
					</div>
				{/if}
				<div class="manufacturerColumn">
				{assign var=columns value=$columns+1}
			{/if}

			<ul>
				<li><a href="{categoryUrl data=$category}">[[category.name]]</a>
				{include file="block/count.tpl" count=$category.count}
			</ul>
			{assign var=index value=$index+1}
		{/foreach}
	{/foreach}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}