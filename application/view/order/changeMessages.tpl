{% if !empty(changes) %}
	{% for type, items in changes %}
		<div style="clear: left;"></div>
		<div class="infoMessage message">
			{% if items|@count > 1 %}
				<div>{translate text="_order_auto_changes_`type`"}:</div>
				<ul>
					{% for item in items %}
						<li>
							{itemsById[item.id].Product.name()}
							{% if 'count' == type %}
								- {maketext text="_order_quantity_change" params="`item.from`,`item.to`"}
							{% endif %}
						</li>
					{% endfor %}
				</ul>
			{% else %}
				{maketext text="_order_auto_changes_single_`type`" params="`itemsById[items.0.id].Product.name()`,`items.0.from`,`items.0.to`"}
			{% endif %}
		</div>
	{% endfor %}
{% endif %}
