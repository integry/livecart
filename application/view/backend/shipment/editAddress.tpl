{form handle=$form action="controller=backend.shipment action=saveAddress id=`$shipmentID`" method="POST"}
	<p>
		<label for="[[type]]_existingAddress_select">{t _use_existing_address}</label>
		{selectfield options=$existingUserAddressOptions id="shipmentAddress_`$shipmentID`_existingAddress_select" name="existingUserAddress" class="existingUserAddress"}
	</p>

	{include file="backend/user/address_edit.tpl" idPrefix="shipmentAddress_`$shipmentID`"}
	<fieldset class="controls" style="width: 400px;">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" name="save" value="{tn _save}" />
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>
{/form}