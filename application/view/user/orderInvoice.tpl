{pageTitle}{t _invoice} #{$order.ID}{/pageTitle}
<div class="userOrderInvoice">

{defun name="address"}
{if $address}
	<p>
		{$address.fullName}
	</p>
	<p>
		{$address.companyName}
	</p>
	<p>
		{$address.address1}
	</p>
	<p>
		{$address.address2}
	</p>
	<p>
		{$address.city}
	</p>
	<p>
		{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
	</p>
	<p>
		{$address.countryName}
	</p>
{/if}
{/defun}

<div id="content" class="left right">

	<div id="invoice">

		<div id="invoiceHeader">

			{img src="image/promo/logo_small.jpg" id="invoiceLogo"}

			<h1>{t _invoice} #{$order.ID}</h1>
			<div id="invoiceDate">{$order.formatted_dateCompleted.date_long}</div>

		</div>

		<div id="invoiceContacts">

			<div class="addressContainer">
				<h2>{t _buyer}</h2>
				{fun name="address" address=$order.BillingAddress}
				{foreach $order.User.attributes as $attr}
					{if $attr.EavField.isDisplayedInList && ($attr.value || $attr.values)}
						<p>
							{$attr.EavField.name_lang}:
							{include file="product/attributeValue.tpl"}
						</p>
					{/if}
				{/foreach}
			</div>

			<div class="addressContainer">
				<h2>{t _seller}</h2>
				<p>
					{'INVOICE_SELLER_INFO'|config}
				</p>
			</div>

		</div>

		<div class="clear"></div>

		{foreach from=$order.shipments item="shipment" name="shipments"}

			{if $shipment.items}

				{if !$shipment.isShippable}
					<h2>{t _downloads}</h2>
				{else}
					<h2>{t _shipment} #{$smarty.foreach.shipments.iteration}</h2>
				{/if}

				{include file="user/shipmentEntry.tpl}

			{/if}

		{/foreach}

		{if 'INVOICE_SHOW_PAYMENT_INFO'|config}
		<h2>{t _payment_info}</h2>

		<table id="invoicePaymentInfo">
			<tr class="itemSubtotal">
				<td>{t _item_subtotal}:</td>
				<td class="amount">{$order.formatted_itemSubtotal}</td>
			</tr>
			<tr class="shippingSubtotal">
				<td>{t _shipping_handling}:</td>
				<td class="amount">{$order.formatted_shippingSubtotal}</td>
			</tr>
			{if $order.taxes}
				<tr class="beforeTaxSubtotal">
					<td>{t _before_tax}:</td>
					<td class="amount">{$order.formatted_subtotalBeforeTaxes}</td>
				</tr>
				{foreach from=$order.taxes[$order.Currency.ID] item=tax}
					<tr class="taxSubtotal">
						<td>{$tax.name_lang}:</td>
						<td class="amount">{$tax.formattedAmount}</td>
					</tr>
				{/foreach}
			{/if}

			{if $order.discountAmount}
				<tr class="discountAmount">
					<td>{t _discount}:</td>
					<td class="amount">{$order.formatted_discountAmount}</td>
				</tr>
			{/if}

			<tr class="grandTotal">
				<td>{t _grand_total}:</td>
				<td class="amount">{$order.formatted_totalAmount}</td>
			</tr>
			<tr class="amountPaid">
				<td>{t _amount_paid}:</td>
				<td class="amount">{$order.formatted_amountPaid}</td>
			</tr>
			<tr class="amountDue">
				<td>{t _amount_due}:</td>
				<td class="amount">{$order.formatted_amountDue}</td>
			</tr>
		</table>
		{/if}

	</div>

</div>

{* include file="layout/frontend/footer.tpl" *}

</div>

<script type="text/javascript">
{*	window.print(); *}
</script>