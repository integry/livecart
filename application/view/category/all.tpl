{pageTitle}{t _all_categories}{/pageTitle}

<div class="manufacturersIndex">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	<h1>{t _all_categories}</h1>

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
				<li><a href="{categoryUrl data=$category}">{$category.name}</a> <span class="count">(&rlm;{$category.count})</span></li>
			</ul>
			{assign var=index value=$index+1}
		{/foreach}
	{/foreach}

</div>

{include file="layout/frontend/footer.tpl"}

</div>