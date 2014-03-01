{% if $product.isFractionalUnit || 'QUANT_FIELD_TYPE'|config == 'QUANT_INPUT' %}
	{textfield name=$field|default:"count" class="quantityInput col-sm-2"}
{% else %}
	{selectfield name=$field|default:"count" options=$quantity class="quantity" noFormat=true}
{% endif %}
