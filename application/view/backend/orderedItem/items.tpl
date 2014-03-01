{% for item in items %}
	<div id="html_[[item.ID]]">
		[[ partial('backend/shipment/itemAmount.tpl', ['item': item]) ]]
	</div>
{% endfor %}