<div>
	<a href="{link controller="backend.customerOrder query="rt=`$randomToken`"}#order_[[order.ID]]__" class="qeInvoiceNumber">{$order.invoiceNumber|escape}</a>
	{% if $statusEditor %}
		<span style="margin-left: 2em;">{t _status}: {selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status"}</span>
	{% endif %}

	<div class="qeOrderSideMenu">
		<div class="qeOrderDate">[[order.formatted_dateCreated.date_medium]] [[order.formatted_dateCreated.time_short]]</div>

		<ul class="menu">
			<li class="order_printInvoice">
				<a href="{link controller="backend.customerOrder" action=printInvoice id=$order.ID}" target="_blank">{t _print_invoice}</a>
			</li>

			<li class="order_printLabel">
				<a href="{link controller="backend.customerOrder" action=printLabels id=$order.ID"}" target="_blank">{t _print_shipping_labels}</a>
			</li>
		</ul>

	</div>
</div>

<table class="qeProducts">
	<tbody>

		{foreach $order.cartItems as $cartIem}
			<tr>
				<td class="qeProduct">
					{% if $cartIem.Product.ID %}
						<a href="{link controller="backend.category query="rt=`$randomToken`"}#product_[[cartIem.Product.ID]]__">{$cartIem.Product.name|escape}</a>
					{% else %}
						[[cartIem.name]]
					{% endif %}
				</td>
				<td class="qeCount">&times;{$cartIem.count|escape}</td>
				<td class="qePrice">{$cartIem.formattedDisplayPrice|escape}</td>
			</tr>
		{/foreach}
	</tbody>
</table>

<div class="qeAddresses">
	{% if $order.ShippingAddress %}
		<p class="shipping">{t _shipping_address}: [[order.ShippingAddress.compact]] </p>
	{% endif %}

	{% if $order.BillingAddress %}
		<p class="billing">{t _billing_address}: [[order.BillingAddress.compact]]</p>
	{% endif %}
</div>