{% if $sectionFilters || 'TOP_FILTER_CONTINUOS'|config %}

	{counter name="topMenuFilterIndex" assign="topMenuFilterIndex"}
	{counter name="lastFilterSelected" assign="lastFilterSelected"}
	{assign var="appliedFilters" value=$sectionFilters.appliedFilters|default:$filters}
	{% if !'TOP_FILTER_RELOAD'|config %}
		{% set action = "boxFilterTopBlock" %}
	{% else %}
		{% set action = "index" %}
	{% endif %}

	{% if 'TOP_FILTER_CONTINUOS'|config && ($lastFilterSelected < $topMenuFilterIndex) %}
		{% set disabled = true %}
	{% endif %}

	{% if !'TOP_MENU_COMPACT'|config %}
		<span class="topMenuFilterCaption {% if $topMenuFilterIndex == 1 %}first{% endif %}">{translate text=$title}</span>
	{% endif %}

	<select {% if !empty(disabled) %}disabled="disabled" class="disabled"{% endif %}>
		<option value="{categoryUrl action=$action data=$category filters=$appliedFilters removeFilters=$sectionFilters.filters}">
			{% if 'TOP_MENU_COMPACT'|config %}
				{translate text=$title}
			{% else %}
				&nbsp;&nbsp;&nbsp;&nbsp;
			{% endif %}
		</option>
		{% if empty(disabled) %}
			{foreach from=$sectionFilters.filters item="filter" name="filters"}
				<option value="{categoryUrl action=$action data=$category filters=$appliedFilters addFilter=$filter removeFilters=$sectionFilters.filters}" {% if $filters[$filter.ID] %}selected="selected" {counter name="lastFilterSelected" assign="lastFilterSelected"}{% endif %}>[[filter.name_lang]]</option>
			{/foreach}
		{% endif %}
	</select>

	{counter name="topMenuFilterIndex" assign="topMenuFilterIndex"}
{% endif %}