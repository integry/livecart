<p>{$order.invoiceNumber|escape}<p>
<p>{$order.formatted_dateCreated.date_medium} {$order.formatted_dateCreated.time_short}</p>
<table>
	<tbody>
		<tr>
			<td>{t _product_name}</td>
			<td>{t _product_count}</td>
			<td>{t _price}</td>
		</tr>
		{foreach $order.cartItems as $cartIem}
			<tr>
				<td>{$cartIem.Product.name|escape}</td>
				<td>&times;{$cartIem.count|escape}</td>
				<td>{$cartIem.formattedDisplayPrice|escape}</td>
			</tr>
		{/foreach}
	</tbody>
</table>
<table>
	<tbody>
		<tr><td>{t _quick_edit_itemSubtotalWithoutTax}</td><td>{$order.formatted_itemSubtotalWithoutTax}</td></tr>
		<tr><td>{t _quick_edit_itemSubtotal}</td><td>{$lastOrder.formatted_itemSubtotal}</td></tr>
		<tr><td>{t _quick_edit_shippingSubtotal}</td><td>{$order.formatted_shippingSubtotal}</td></tr>
		<tr><td>{t _quick_edit_itemDiscount}</td><td>{$order.formatted_itemDiscount}</td></tr>
		<tr><td>{t _quick_edit_subtotalBeforeTaxes}</td><td>{$order.formatted_subtotalBeforeTaxes}</td></tr>
		<tr><td>{t _quick_edit_totalAmount}</td><td>{$order.formatted_totalAmount}</td></tr>
	</tbody>
</table>
<p>
	{t _shipping_address}:
	{$order.ShippingAddress.compact}
</p>
<p>
	{t _billing_address}:
	{$order.BillingAddress.compact}
</p>
