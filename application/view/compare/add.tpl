{% if $added && ($products|@count == 1) %}
	{include file="block/compareMenu.tpl" return=$return}
{% elseif $added %}
	{include file="compare/block/item.tpl" product=$products[$added]}
{% endif %}