{if $sectionFilters || 'TOP_FILTER_CONTINUOS'|config}

	{counter name="topMenuFilterIndex" assign="topMenuFilterIndex"}
	{counter name="lastFilterSelected" assign="lastFilterSelected"}
	{assign var="appliedFilters" value=$sectionFilters.appliedFilters|default:$filters}
	{if !'TOP_FILTER_RELOAD'|config}
		{% set action = "boxFilterTopBlock" %}
	{else}
		{% set action = "index" %}
	{/if}

	{if 'TOP_FILTER_CONTINUOS'|config && ($lastFilterSelected < $topMenuFilterIndex)}
		{% set disabled = true %}
	{/if}

	{if !'TOP_MENU_COMPACT'|config}
		<span class="topMenuFilterCaption {if $topMenuFilterIndex == 1}first{/if}">{translate text=$title}</span>
	{/if}

	<select {if $disabled}disabled="disabled" class="disabled"{/if}>
		<option value="{categoryUrl action=$action data=$category filters=$appliedFilters removeFilters=$sectionFilters.filters}">
			{if 'TOP_MENU_COMPACT'|config}
				{translate text=$title}
			{else}
				&nbsp;&nbsp;&nbsp;&nbsp;
			{/if}
		</option>
		{if !$disabled}
			{foreach from=$sectionFilters.filters item="filter" name="filters"}
				<option value="{categoryUrl action=$action data=$category filters=$appliedFilters addFilter=$filter removeFilters=$sectionFilters.filters}" {if $filters[$filter.ID]}selected="selected" {counter name="lastFilterSelected" assign="lastFilterSelected"}{/if}>[[filter.name_lang]]</option>
			{/foreach}
		{/if}
	</select>

	{counter name="topMenuFilterIndex" assign="topMenuFilterIndex"}
{/if}