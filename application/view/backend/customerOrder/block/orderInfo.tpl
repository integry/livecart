<div><a href="{link controller=backend.customerOrder query="rt=`$randomToken`"}#order_{$order.ID}__">{$order.invoiceNumber|escape}</a>
<span style="float:right;">({$order.formatted_dateCreated.date_medium} {$order.formatted_dateCreated.time_short})</span></div>

{if $statusEditor}
	<p>{selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status"}</p>
{/if}

<table class="qeProducts">
	<tbody>
		{*<tr>
			<td>{t _product_name}</td>
			<td>{t _product_count}</td>
			<td class="qePrice">{t _price}</td>
		</tr>*}
		{foreach $order.cartItems as $cartIem}
			<tr>
				<td><a href="{link controller=backend.category query="rt=`$randomToken`"}#product_{$cartItems.productID}__">{$cartIem.Product.name|escape}</a></td>
				<td>&times;{$cartIem.count|escape}</td>
				<td class="qePrice">{$cartIem.formattedDisplayPrice|escape}</td>
			</tr>
		{/foreach}
	</tbody>
</table>
<div class="clear"></div>
<table class="qeOrderPrices">
	<tbody>
		<tr><td class="qeRight">{t _quick_edit_itemSubtotalWithoutTax}:</td><td class="qePrice">{$order.formatted_itemSubtotalWithoutTax}</td></tr>
		<tr><td class="qeRight">{t _quick_edit_shippingSubtotal}:</td><td class="qePrice">{$order.formatted_shippingSubtotal}</td></tr>
		<tr><td class="qeRight">{t _quick_edit_subtotalBeforeTaxes}:</td><td class="qePrice">{$order.formatted_subtotalBeforeTaxes}</td></tr>
		<tr><td class="qeRight">{t _quick_edit_itemDiscount}:</td><td class="qePrice">{$order.formatted_itemDiscount}</td></tr>
		<tr><td class="qeRight">{t _quick_edit_totalAmount}:</td><td class="qePrice qeStrong">{$order.formatted_totalAmount}</td></tr>
	</tbody>
</table>

<div class="clear"></div>
<p>{t _shipping_address}: {$order.ShippingAddress.compact} </p>
<p>{t _billing_address}: {$order.BillingAddress.compact}</p>