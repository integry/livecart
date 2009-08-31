<fieldset class="orderShipment_controls {if $order.isMultiAddress}multiAddress{/if} error" {if $notShippable}style="display: none;"{/if}>
   <fieldset class="orderShipment_status error">
	   <label>{t _status}: </label>
	   <select name="status" id="orderShipment_status_{$shipment.ID}" {denied role='order.update'}disabled="disabled"{/denied}">
		   {foreach key="statusID" item="status" from=$statuses}
			   <option value="{$statusID}" id="orderShipment_status_{$shipment.ID}_{$statusID}" {if $shipment.status == $statusID}selected{/if}>{$status}</option>
		   {/foreach}
	   </select>
	   {img style="display: none" id="orderShipment_status_`$shipment.ID`_feedback" src="image/indicator.gif"}
   </fieldset>

   {if $order.isMultiAddress}
		<fieldset class="shipmentAddress">
			<legend>{t _shipping_address}</legend>
			<div class="menu">
				<a href="#" onclick="Backend.Shipment.prototype.getInstance(Event.element(event).up('.orderShipment')).editShippingAddress(); return false;">{t _edit_address}</a>
				<span class="progressIndicator" style="display: none;"></span>
			</div>
			<div class="viewAddress">
				{$shipment.ShippingAddress.compact}
			</div>
			<div class="editAddress">
			</div>
		</fieldset>
   {/if}

   {block SHIPMENT-CONTROLS}

</fieldset>