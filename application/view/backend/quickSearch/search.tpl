{foreach $classNames as $className}
	{if $result[$className].count > 0}
		<div class="qsResultsContainer qs{$className}">
			<h3 class="qsClassName">{t _title_`$className`}</h3>
			<span class="qsCount">({$result[$className].count})</span>
			<ul>
				{foreach $result[$className].records as $record}
				<li>
					{include file="backend/quickSearch/result_`$className`.tpl"}
				</li>
				{/foreach}
			</ul>
			{include file="backend/quickSearch/paginate.tpl"
				from=$result[$className].from
				to=$result[$className].to
				count=$result[$className].count
				class=$className}
		</div>
		<div class="qsSeperator"></div>
		{assign var="hasResult" value=true}
	{/if}
{/foreach}
{if $fullSearch}{*searching by all classes *}
	{if !$hasResult}
		<div class="qsNothingFound">
			{t _nothing_found_for_query}: <strong>{$query}</strong>
		</div>
	{/if}
	<a class="cancel" href="javascript:void(0)" onclick="Backend.QuickSearch.hideResultContainer()">{t _cancel}</a>
{/if}