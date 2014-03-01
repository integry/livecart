[[ partial("checkout/block/ccForm.tpl") ]]

{% if !empty(otherMethods) %}
	{% if config('CC_ENABLE') %}
		<h2>{t _other_methods}</h2>
	{% else %}
		<h2>{t _select_payment_method}</h2>
	{% endif %}

	<div id="otherMethods">
		{% for method in otherMethods %}
			{% if !empty(id) %}{assign var="query" value="order=`id`"}{% endif %}
			<a href="[[ url("checkout/redirect/" ~ method, "query") ]]"><img src="{s image/payment/[[method]].gif}" /></a>
		{% endfor %}
	</div>
{% endif %}
