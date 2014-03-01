{foreach $cart.taxes.$currency as $tax}
	<tr>
		<td colspan="{math equation="$extraColspanSize + 3"}" class="subTotalCaption">[[tax.name()]]:</td>
		<td class="amount taxAmount">[[tax.formattedAmount]]</td>
		[[ partial("order/block/cartUpdate.tpl") ]]
	</tr>
{/foreach}
