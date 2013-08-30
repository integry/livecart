{foreach $modelSearch as $results}
	{% if $results.count %}
		<div class="modelSearchResults">
			<div class="resultStats">{maketext text="_found_x" params=$results.meta.name} {include file="block/count.tpl" count=$results.count}</div>

			<ol>
				{foreach $results.records as $record}
					{include file=$results.meta.template}
				{/foreach}
			</ol>

			{% if $results.count > 'SEARCH_MODEL_PREVIEW'|@config %}
				<div class="allResults">
					<a href="{link controller=search action=index query="type=`$results.meta.class`&q=`req('q')`"}">{maketext text=_all_results params=$results.count}</a>
				</div>
			{% endif %}
		</div>
	{% endif %}
{/foreach}