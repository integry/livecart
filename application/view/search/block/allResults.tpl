{foreach $modelSearch as $results}
	{if $results.count}
		<div class="modelSearchResults">
			<div class="resultStats">{maketext text="_found_x" params=$results.meta.name} <span class="count">({$results.count})</span></div>

			<ol>
				{foreach $results.records as $record}
					{include file=$results.meta.template}
				{/foreach}
			</ol>

			{if $results.count > 'SEARCH_MODEL_PREVIEW'|@config}
				<div class="allResults">
					<a href="{link controller=search action=index query="type=`$results.meta.class`&q=`$request.q`"}">{maketext text=_all_results params=$results.count}</a>
				</div>
			{/if}
		</div>
	{/if}
{/foreach}