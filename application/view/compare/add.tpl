{% if $added && ($products|@count == 1) %}
	[[ partial('block/compareMenu.tpl', ['return': $return]) ]]
{% elseif $added %}
	[[ partial('compare/block/item.tpl', ['product': $products[$added]]) ]]
{% endif %}