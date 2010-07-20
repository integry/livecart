<span class="realPrice">{$product.formattedPrice.$currency}</span>
{if $product.formattedListPrice.$currency}
	<span class="listPrice">
		{$product.formattedListPrice.$currency}
	</span>
{/if}
