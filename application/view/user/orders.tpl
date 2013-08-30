{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_orders}{{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "orderMenu"]) ]]
{% block content %}

	<div class="resultStats">
		{% if !empty(orders) %}
			{% if $count > $perPage %}
				{maketext text=_displaying_orders params="`$from`,`$to`,`$count`"}
			{% else %}
				{maketext text=_orders_found params=$count}
			{% endif %}
		{% else %}
			{t _no_orders_found}
		{% endif %}
	</div>

	{foreach from=$orders item="order"}
		[[ partial('user/orderEntry.tpl', ['order': order]) ]]
	{/foreach}

	{% if $count > $perPage %}
		{capture assign="url"}{link controller=user action=orders id=0}{/capture}
		{paginate current=$currentPage count=$count perPage=$perPage url=$url}
	{% endif %}

{% endblock %}



</div>
