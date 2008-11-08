{pageTitle}{t _manufacturers}{/pageTitle}

<div class="manufacturersIndex">

{include file="layout/frontend/layout.tpl"}

<div id="content">

	<h1>{t _manufacturers}</h1>

	{foreach from=$manufacturers item=manufacturer key=index}
		{if $lastLetter != $manufacturer.name.0|@capitalize}
			{if !$index || (($manufacturers|@count/2) <= $index && $columns < 2)}
				{if $columns}
					</div>
				{/if}
				<div class="manufacturerColumn">
				{assign var=columns value=$columns+1}
			{/if}

			<h2>{$manufacturer.name.0}</h2>
		{/if}
		<ul>
			<li><a href="{categoryUrl data=$rootCat addFilter=$manufacturer.filter}">{$manufacturer.name}</a> <span class="count">(&rlm;{$counts[$manufacturer.ID]})</span></li>
		</ul>
		{assign var=lastLetter value=$manufacturer.name.0|@capitalize}
	{/foreach}

</div>

{include file="layout/frontend/footer.tpl"}

</div>