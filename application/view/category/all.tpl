{pageTitle}{t _all_categories}{/pageTitle}

<div class="manufacturersIndex">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	<h1>{t _all_categories}</h1>

	{foreach from=$categories item=category key=index}
		{if $lastLetter != $category.name.0|@capitalize}
			{if !$index || (($categories|@count/2) <= $index && $columns < 2)}
				{if $columns}
					</div>
				{/if}
				<div class="manufacturerColumn">
				{assign var=columns value=$columns+1}
			{/if}

			<h2>{$category.name.0}</h2>
		{/if}
		<ul>
			<li><a href="{categoryUrl data=$category}">{$category.name}</a> <span class="count">({$category.count})</span></li>
		</ul>
		{assign var=lastLetter value=$category.name.0|@capitalize}
	{/foreach}

</div>

{include file="layout/frontend/footer.tpl"}

</div>