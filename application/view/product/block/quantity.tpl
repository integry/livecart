{if !$product.isFractionalUnit}
	{selectfield name=$field|default:"count" options=$quantity}
{else}
	{textfield name=$field|default:"count" class="text number"}
{/if}
