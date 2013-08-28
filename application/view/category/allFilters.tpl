{math count=$allFilters.filters|@count equation="max(2, ceil(count / 3))" assign="perColumn"}

<fieldset class="allFilters">

	{if 'brand' == $showAll}
		<legend>{t _by_brand}</legend>
	{else}
		<legend>[[allFilters.name_lang]]</legend>
	{/if}

	{foreach from=$allFilters.filters item=filter name="filters"}

		{if $smarty.foreach.filters.iteration % $perColumn == 1}
			<div class="filterGroup">
				<ul>
		{/if}

		<li>
			<a href="{categoryUrl data=$category filters=$filters addFilter=$filter query="showAll=$showAll"}">[[filter.name_lang]]</a>&nbsp;{include file="block/count.tpl" count=$filter.count}
		</li>

		{if $smarty.foreach.filters.iteration % $perColumn == 0 || $smarty.foreach.filters.last}
				</ul>
			</div>
		{/if}

	{/foreach}

</fieldset>