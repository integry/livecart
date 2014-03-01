{% if expressMethods && cart.isOrderable && !cart.isMultiAddress %}
	<div id="expressCheckoutMethods">
		{% for method in expressMethods %}
			<a href="[[ url("checkout/express/" ~ method) ]]"><img src="{s image/payment/[[method]].gif}" /></a>
		{% endfor %}
	</div>
{% endif %}
