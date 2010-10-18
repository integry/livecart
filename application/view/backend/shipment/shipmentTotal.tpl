<form>

<input type="hidden" name="ID" value="{$shipment.ID|default:$shipmentID}" />
<input type="hidden" name="orderID" value="{$shipment.Order.ID|default:$orderID}" />
<input type="hidden" name="shippingServiceID" value="{$shipment.ShippingService.ID|default:$shippingServiceID}" />
<input type="hidden" name="downloadable" value="{$downloadable|default:0}" />

<table class="orderShipmentsItem_info orderShipment_info">
	<tr class="orderShipment_info_first_row orderShipment_info_subtotal_row {zebra}">
		<td class="orderShipmentsItem_info_report_td">
			<div class="orderShipmentsItem_info_report">
				{t _subtotal_price}:
			</div>
		</td>
		<td class="orderShipmentsItem_info_total_td">
			<div class="orderShipmentsItem_info_total">
				<span class="orderShipment_info_subtotal shipment_amount">
					<span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
					<span class="price">{$shipment.amount|default:0|string_format:"%.2f"}</span>
					<span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
				</span>
			</div>
		</td>
	</tr>

	<tr class="orderShipment_info_shipping_row {zebra}">
		<td class="orderShipmentsItem_info_report_td">
			<div class="orderShipmentsItem_info_report">
				<span class="progressIndicator" style="display: none;"></span>
				{t _shipping}:
				<a href="#change" class="orderShipment_change_usps" id="orderShipment_change_usps_{$shipment.ID}"  style="{if $shipment.status == 3}display: none;{/if} {denied role='order.update'}display: none{/denied}">{$shipment.ShippingService.name_lang|default:$shippingServiceIsNotSelected}</a>
				{denied role='order.update'}<b>{$shipment.ShippingService.name_lang|default:$shippingServiceIsNotSelected}</b>{/denied}
				<span class="controls" id="orderShipment_USPS_{$shipment.ID}" style="display: none">
					<select name="USPS" id="orderShipment_USPS_{$shipment.ID}_select" class="orderShipment_USPS_select">
						{if $shipment.ShippingService.ID|default:$shippingServiceID}
						<option value="{$shipment.ShippingService.ID|default:$shippingServiceID}" selected="selected">{$shipment.ShippingService.name_lang|default:$shippingServiceIsNotSelected}</option>
						{/if}
					</select>

					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" value="{t _save}" class="button submit"  id="orderShipment_USPS_{$shipment.ID}_submit" />
					or
					<a class="cancel" href="#cancel"  id="orderShipment_USPS_{$shipment.ID}_cancel" >Cancel</a>
				</span>
			</div>
		</td>
		<td class="orderShipmentsItem_info_total_td">
			<div class="orderShipmentsItem_info_total">
				<span class="orderShipment_info_shippingAmount shipment_shippingAmount">
					<span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
					<span class="price">
						<input type="text" class="text number shippingAmount" name="shippingAmount[{$shipment.ID}]" value="{$shipment.shippingAmount|default:0|string_format:"%.2f"}" />
					</span>
					<span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
				</span>
			</div>
		</td>
	</tr>

	<tr class="orderShipment_info_tax_row {zebra}">
		<td class="orderShipmentsItem_info_report_td">
			<div class="orderShipmentsItem_info_report">
				{t _taxes}:
			</div>
		</td>
		<td class="orderShipmentsItem_info_tax_td">
			<div class="orderShipmentsItem_info_tax">
				<span class="orderShipment_info_subtotal shipment_taxAmount">
					<span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
					<span class="price">{$shipment.taxAmount|default:0|string_format:"%.2f"}</span>
					<span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
				</span>
			</div>
		</td>
	</tr>

	<tr class="orderShipment_info_total_row {zebra}">
		<td class="orderShipmentsItem_info_report_td">
			<div class="orderShipmentsItem_info_report">
				{t _price}:
			</div>
		</td>
		<td class="orderShipmentsItem_info_total_td">
			<div class="orderShipmentsItem_info_total orderShipment_totalSum">
				<span class="orderShipment_info_total shipment_total">
					<span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
					<span class="price">{assign var="price" value=$shipment.totalAmount}{$price|string_format:"%.2f"}</span>
					<span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
				</span>
			</div>
		</td>
	</tr>
</table>

</form>
