{foreach from=$classNames key=key item=className}
	{if $result[$className].count > 0}
		<div class="qsResultsContainer qs{$className}">
			<h3 class="qsClassName{if 0 == $key} first{/if}">
				{if $className == 'SearchableItem'}
				{* <span class="qsName">{t _title_`$className`}</span> *}
					{t _title_SearchableConfiguration}
				{else}
					{t _title_`$className`}
				{/if}
			</h3>
				<span class="qsCount">({$result[$className].count})</span>
			</h3>
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
	<a class="cancel" href="javascript:void(0)"
	onclick="Backend.QuickSearch.getInstance(this).hideResultContainer()">{t _cancel}</a>
{/if}