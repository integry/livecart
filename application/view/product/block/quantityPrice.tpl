<table class="quantityPrice">
	{foreach from=$quantityPricing item=quantityPrice key=quant name=quant}
		<tr class="{zebra loop=quant}">
			<td class="quantityAmount">
				{if $quantityPrice.to}
					{$quantityPrice.from} - {$quantityPrice.to}
				{else}
					{maketext text="_x_or_more" params=$quantityPrice.from}
				{/if}
			</td>
			<td class="price quantityPrice">
				{$quantityPrice.formattedPrice}
			</td>
		</tr>
	{/foreach}
</table>
