{% if $letters|count > 0 %}
	<ul class="manufacturerLetterFilter">
		<li class="label">{t _filter}:</li>
		{foreach from=$letters item=letter}
			<li>
				{% if $letter == $currentLetter %}
					<strong class="selectedLetter">[[letter]]</strong>
				{% else %}
					<a href="{link controller=manufacturers query="letter=[[letter]]"}">[[letter]]</a>
				{% endif %}
			</li>
		{/foreach}
	</ul>
{% endif %}


{assign var=numberOfColumns value='MANUFACTURER_PAGE_NUMBER_OF_COLUMNS'|config}
<style type="text/css">
	.manufacturerColumn
	{literal}{{/literal}
		width: {math equation="(100-2.5*x)/x" x=$numberOfColumns}%; {* .manufacturerColumn has 2.5% left margin *}
	{literal}}{/literal}
</style>
{assign var=columns value=0}
{foreach from=$manufacturers item=manufacturer key=index}

		{% if !$index || (($manufacturers|@count/$numberOfColumns * $columns ) <= $index && $columns < $numberOfColumns ) %}
			{% if $columns %}
				{assign var=opened value=false}
				</div>
			{% endif %}
			<div class="manufacturerColumn">
			{assign var=opened value=true}
			{assign var=columns value=$columns+1}
		{% endif %}
	<ul>
		<li><a href="[[manufacturer.url]]">[[manufacturer.name]]</a>
		[[ partial('block/count.tpl', ['count': counts[$manufacturer.ID]]) ]]
	</ul>
{/foreach}

{% if opened %}
</div>
{% endif %}
