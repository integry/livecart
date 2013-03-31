{pageTitle}{$results.meta.name|capitalize} &gt;&gt; "{$query}"{/pageTitle}

{include file="layout/frontend/layout.tpl"}
{include file="block/content-start.tpl"}

	<div class="modelSearchResults">
		<div class="resultStats">{maketext text="_found_x" params=$results.meta.name} <span class="count">({$results.count})</span></div>

		<ol>
			{foreach $results.records as $record}
				{include file=$results.meta.template}
			{/foreach}
		</ol>

	</div>

	{if $results.count > $perPage}
		<div class="resultPages">
			<span>{t _pages}:</span> {paginate current=$page count=$results.count perPage=$perPage url=$url}
		</div>
	{/if}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}