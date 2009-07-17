{if $cart.shippingSubtotal}
	<tr>
		<td colspan="3" class="subTotalCaption">
			{if $isShippingEstimated}
				{t _estimated_shipping}:
			{else}
				{t _shipping}:
			{/if}
		</td>
		<td class="amount shippingAmount">{$cart.formatted_shippingSubtotal}</td>
		{$GLOBALS.cartUpdate|@array_shift}
	</tr>
{/if}
