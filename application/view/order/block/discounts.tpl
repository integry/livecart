{foreach from=$cart.discounts item=discount}
	{if $discount.amount != 0}
		<tr>
			<td colspan="3" class="subTotalCaption">{if $discount.amount > 0}{t _discount}{else}{t _surcharge}{/if}: <span class="discountDesc">{$discount.description}</span></td>
			<td class="amount discountAmount">{$discount.formatted_amount}</td>
			{$GLOBALS.cartUpdate|@array_shift}
		</tr>
	{/if}
{/foreach}

{if $cart.itemDiscountReverse}
	{if $discount.amount != 0}
		<tr>
			<td colspan="3" class="subTotalCaption">{if $cart.itemDiscountReverse < 0}{t _discount}{else}{t _surcharge}{/if}:</td>
			<td class="amount discountAmount">{$cart.formatted_itemDiscountReverse}</td>
			{$GLOBALS.cartUpdate|@array_shift}
		</tr>
	{/if}
{/if}