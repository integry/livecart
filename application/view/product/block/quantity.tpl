{if $product.isFractionalUnit || 'QUANT_FIELD_TYPE'|config == 'QUANT_INPUT'}
	{textfield name=$field|default:"count" class="text number"}
{else}
	{selectfield name=$field|default:"count" options=$quantity}
{/if}
