{if $fullSearch}
<a class="cancel" href="javascript:void(0)" style="float: right;"
	onclick="Backend.QuickSearch.getInstance(this).hideResultContainer()">{t _cancel}</a>
{/if}

{foreach from=$classNames key=key item=className name="qsClasses"}
	{if $result[$className].count > 0}
		<div class="qsResultsContainer qs{$className}">
			<h3 class="qsClassName{if !$hasResult} first{/if}">
				{if $className == 'SearchableItem'}
					{t _title_SearchableConfiguration}
				{else}
					{t _title_`$className`}
				{/if}
				<span class="qsCount">({$result[$className].count})</span>
			</h3>

			<ul>
				{foreach $result[$className].records as $record}
				<li>
					{if $customResultTemplates[$className]}
						{include file="backend/quickSearch/result_`$customResultTemplates[$className]`.tpl"}
					{else}
						{include file="backend/quickSearch/result_`$className`.tpl"}
					{/if}
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
{/if}