{% if $added && ($products|@count == 1) %}
	[[ partial('block/compareMenu.tpl', ['return': return]) ]]
{% elseif !empty(added) %}
	[[ partial('compare/block/item.tpl', ['product': products[$added]]) ]]
{% endif %}