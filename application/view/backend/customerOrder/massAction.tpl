<span style="{denied role="order.mass"}visibility: hidden;{/denied}" id="orderMass_{$orderGroupID}" class="activeGridMass">

	{form action="controller=backend.customerOrder action=processMass id=$orderGroupID" method="POST" handle=$massForm onsubmit="return false;"}

	<input type="hidden" name="filters" value="" />
	<input type="hidden" name="selectedIDs" value="" />
	<input type="hidden" name="isInverse" value="" />

	{t _with_selected}:
	<select name="act" class="select">
		{if $orderGroupID == 8}
			<option value="setFinalized">{t _mark_completed}</option>
		{/if}
		{if $orderGroupID < 8}
			<option value="printLabels" rel="blank">{t _print_shipping_labels}</option>
			<optgroup label="{t _order_status}" class="massStatus">
				<option value="setNew">{t _set_new}</option>
				<option value="setProcessing">{t _set_processing}</option>
				<option value="setAwaitingShipment">{t _set_awaiting_shipment}</option>
				<option value="setShipped">{t _set_shipped}</option>
				<option value="setReturned">{t _set_returned}</option>
			</optgroup>
			<option value="setCancel" class="massCancel">{t _cancel}</option>
		{/if}
		<option value="delete" class="delete">{t _delete}</option>
	</select>

	<span class="bulkValues" style="display: none;">

	</span>

	<input type="submit" value="{tn _process}" class="submit" />
	<span class="progressIndicator" style="display: none;"></span>

	{/form}

</span>