{hidden name="`$namePrefix`ID"}

<fieldset class="error">
	<label for="{$idPrefix}_firstName">{t _first_name}</label>
	{textfield name="`$namePrefix`firstName" id="`$idPrefix`_firstName" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_lastName">{t _last_name}</label>
	{textfield name="`$namePrefix`lastName" id="`$idPrefix`_lastName" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_companyName">{t _company}</label>
	{textfield name="`$namePrefix`companyName" id="`$idPrefix`_companyName" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_countryID">{t _country}</label>
	{selectfield name="`$namePrefix`countryID" options=$countries id="`$idPrefix`_countryID" class="country"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_stateID">{t _state}</label>
	{selectfield name="`$namePrefix`stateID" options=$states id="`$idPrefix`_stateID" class="state"}
	{textfield name="`$namePrefix`stateName" id="`$idPrefix`_stateName"}
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_city">{t _city}</label>
	{textfield name="`$namePrefix`city" id="`$idPrefix`_city" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_address1">{t _address} 1</label>
	{textfield name="`$namePrefix`address1" id="`$idPrefix`_address1" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_address2">{t _address} 2</label>
	{textfield name="`$namePrefix`address2" id="`$idPrefix`_address2" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_postalCode">{t _postal_code}</label>
	{textfield name="`$namePrefix`postalCode" id="`$idPrefix`_postalCode" class="text"}
	<div class="errorText" style="display: none" ></div>
</fieldset>

<fieldset class="error">
	<label for="{$idPrefix}_phone">{t _phone}</label>
	{textfield name="`$namePrefix`phone" id="`$idPrefix`_phone" class="text"}
</fieldset>

{include file="block/eav/fields.tpl" item=$address fieldList=$specFieldListByOwner.UserAddress[$address.ID]}

<script type="text/javascript">
$('{$idPrefix}_stateID').stateSwitcher = new Backend.User.StateSwitcher(
		$('{$idPrefix}_countryID'),
		$('{$idPrefix}_stateID'),
		$('{$idPrefix}_stateName'),
		'{link controller=backend.user action=states}'
	);
</script>