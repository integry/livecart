{assign var=numberOfColumns value='MANUFACTURER_PAGE_NUMBER_OF_COLUMNS'|config}
<style type="text/css">
	.manufacturerColumn
	{literal}{{/literal}
		width: {math equation="(100-2.5*x)/x" x=$numberOfColumns}%; {* .manufacturerColumn has 2.5% left margin *}
	{literal}}{/literal}
</style>
{assign var=columns value=0}
{foreach from=$manufacturers item=manufacturer key=index}
	{% if $lastLetter != $manufacturer.name.0|@capitalize %}
		{% if !$index || (($manufacturers|@count/$numberOfColumns * $columns ) <= $index && $columns < $numberOfColumns ) %}
			{% if $columns %}
				{assign var=opened value=false}
				</div>
			{% endif %}
			<div class="manufacturerColumn">
			{assign var=opened value=true}
			{assign var=columns value=$columns+1}
		{% endif %}
		<h2>[[manufacturer.name.0]]</h2>
	{% endif %}
	<ul>
		<li><a href="[[manufacturer.url]]">[[manufacturer.name]]</a>
		[[ partial('block/count.tpl', ['count': $counts[$manufacturer.ID]]) ]]
	</ul>
	{assign var=lastLetter value=$manufacturer.name.0|@capitalize}
{/foreach}
{% if $opened %}
	</div>
{% endif %}
