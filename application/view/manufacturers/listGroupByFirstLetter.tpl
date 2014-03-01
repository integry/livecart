{% if letters|count > 0 %}
	<ul class="manufacturerLetterFilter">
		<li class="label">{t _filter}:</li>
		{% for letter in letters %}
			<li>
				{% if letter == currentLetter %}
					<strong class="selectedLetter">[[letter]]</strong>
				{% else %}
					<a href="{link controller=manufacturers query="letter=[[letter]]"}">[[letter]]</a>
				{% endif %}
			</li>
		{% endfor %}
	</ul>
{% endif %}


{assign var=numberOfColumns value=config('MANUFACTURER_PAGE_NUMBER_OF_COLUMNS')}
<style type="text/css">
	.manufacturerColumn
	{
		width: {math equation="(100-2.5*x)/x" x=numberOfColumns}%; {* .manufacturerColumn has 2.5% left margin *}
	}
</style>
{assign var=columns value=0}
{foreach from=manufacturers item=manufacturer key=index}

		{% if !index || ((manufacturers|@count/numberOfColumns * columns ) <= index && columns < numberOfColumns ) %}
			{% if !empty(columns) %}
				{assign var=opened value=false}
				</div>
			{% endif %}
			<div class="manufacturerColumn">
			{assign var=opened value=true}
			{assign var=columns value=columns+1}
		{% endif %}
	<ul>
		<li><a href="[[manufacturer.url]]">[[manufacturer.name]]</a>
		[[ partial('block/count.tpl', ['count': counts[manufacturer.ID]]) ]]
	</ul>
{% endfor %}

{% if opened %}
</div>
{% endif %}
