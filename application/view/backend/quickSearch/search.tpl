{% if $fullSearch %}
<a class="cancel" href="javascript:void(0)" style="float: right;"
	onclick="Backend.QuickSearch.getInstance(this).hideResultContainer()">{t _cancel}</a>
{% endif %}

{foreach from=$classNames key=key item=className name="qsClasses"}
	{% if $result[$className].count > 0 %}
		{% if $fullSearch %}
			<div class="qsResultsContainer qs[[className]]">
			<h3 class="qsClassName{% if 0 == $key %} first{% endif %}">
				{% if $className == 'SearchableItem' %}
				{* <span class="qsName">{t _title_`$className`}</span> *}
					{t _title_SearchableConfiguration}
				{% else %}
					{t _title_`$className`}
				{% endif %}
				<span class="qsCount">({$result[$className].count})</span>
			</h3>
		{% endif %}
		<div class="qsResultsContainer">
		<ul>
			{foreach $result[$className].records as $record}
			<li>
				{% if $customResultTemplates[$className] %}
					{include file="backend/quickSearch/result_`$customResultTemplates[$className]`.tpl"}
				{% else %}
					{include file="backend/quickSearch/result_`$className`.tpl"}
				{% endif %}
			</li>
			{/foreach}
		</ul>

		{include file="backend/quickSearch/paginate.tpl"
			from=$result[$className].from
			to=$result[$className].to
			count=$result[$className].count
			class=$className}
		</div>

		{% if $fullSearch %}
			</div>
			<div class="qsSeperator"></div>
		{% endif %}

		{% set hasResult = true %}
	{% endif %}
{/foreach}

{% if $fullSearch %}
	{% if !$hasResult %}
		<div class="qsNothingFound">
			{t _nothing_found_for_query}: <strong>[[query]]</strong>
		</div>
	{% endif %}
{% endif %}
