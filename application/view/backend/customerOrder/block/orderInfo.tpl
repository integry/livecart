<div><a href="{link controller=backend.customerOrder query="rt=`$randomToken`"}#order_{$order.ID}__">{$order.invoiceNumber|escape}</a>
<span style="float:right;">({$order.formatted_dateCreated.date_medium} {$order.formatted_dateCreated.time_short})</span></div>

{if $statusEditor}
	<p>{selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status" onchange="return ActiveGrid.QuickEdit.onSubmit(this);"}</p>
{/if}

<table class="qeProducts">
	<tbody>

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
		{include file="order/orderTableDetails.tpl" hideTaxes=true shipment=$order}
	</tbody>
</table>

<div class="clear"></div>
<p>{t _shipping_address}: {$order.ShippingAddress.compact} </p>
<p>{t _billing_address}: {$order.BillingAddress.compact}</p>