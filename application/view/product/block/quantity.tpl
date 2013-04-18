{if $product.isFractionalUnit || 'QUANT_FIELD_TYPE'|config == 'QUANT_INPUT'}
	{textfield name=$field|default:"count" class="quantityInput col-span-2"}
{else}
	{selectfield name=$field|default:"count" options=$quantity class="quantity"}
{/if}
