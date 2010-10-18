<fieldset class="shipmentContainer">
{if $shipment.isShippable}
	<legend>{t _shipment} #{$shipment.ID}</legend>
{/if}

	<div class="shipmentStatus_{$shipment.status}">
		{include file="backend/shipment/shipmentControls.tpl" status=$shipment.status notShippable=$notShippable}

		<table class="orderShipmentsItem_info shipmentTableHeading" style="font-size: smaller; display: table;">
			<tbody>
			  <tr>
				<td class="orderShipmentsItem_info_sku_td">
					<div class="orderShipmentsItem_info_sku">
						{t _sku}
					</div>
				</td>
				<td class="orderShipmentsItem_info_name_td">
					<div class="orderShipmentsItem_info_name">
						{t _name}
					</div>
				</td>
				<td class="orderShipmentsItem_info_price_td">
					<div class="orderShipmentsItem_info_price">
						{t _item_price}
					</div>
				</td>
				<td class="orderShipmentsItem_info_count_td">
					<div class="orderShipmentsItem_info_count">
						{t _quantity}
					</div>
				</td>
				<td class="orderShipmentsItem_info_total_td">
					<div class="orderShipmentsItem_info_total item_subtotal">
						{t _subtotal}
					</div>
				</td>
			  </tr>
			</tbody>
		</table>

		<ul id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}" class="{if $shipment.status != 3 && $shipableShipmentsCount > 1 && $shipment.isShippable}activeList_add_sort{/if} activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem ohoho_{$shipment.ID}">
		{foreach item="item" from=$shipment.items}
			<li id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}_{$item.ID}" >
				{include file="backend/shipment/itemAmount.tpl" shipped=false}
			</li>
		{/foreach}
		</ul>

		{include file="backend/shipment/shipmentTotal.tpl"}
	</div>

</fieldset>