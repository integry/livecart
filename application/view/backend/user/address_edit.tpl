{hidden name="`$namePrefix`ID"}

{% if empty(hideName) %}
[[ textfld('`$namePrefix`firstName', '_first_name') ]]

[[ textfld('`$namePrefix`lastName', '_last_name') ]]

[[ textfld('`$namePrefix`companyName', '_company') ]]
{% endif %}

{input name="`$namePrefix`countryID"}
	{label}{t _country}:{/label}
	{selectfield options=$countries class="country" id="`$idPrefix`_countryID"}
{/input}

{input name="`$namePrefix`stateID"}
	{label}{t _state}:{/label}
	{selectfield options=$states id="`$idPrefix`_stateID" class="state"}
	{textfield id="`$idPrefix`_stateName"}
{/input}

[[ textfld('`$namePrefix`city', '_city') ]]

[[ textfld('`$namePrefix`address1', '_address') ]]

[[ textfld('`$namePrefix`address2', '_address') ]]

[[ textfld('`$namePrefix`postalCode', '_postal_code') ]]

[[ textfld('`$namePrefix`phone', '_phone') ]]

[[ partial('block/eav/fields.tpl', ['item': address, 'fieldList': specFieldListByOwner.UserAddress[$address.ID]]) ]]

<script type="text/javascript">
$('[[idPrefix]]_stateID').stateSwitcher = new Backend.User.StateSwitcher(
		$('[[idPrefix]]_countryID'),
		$('[[idPrefix]]_stateID'),
		$('[[idPrefix]]_stateName'),
		'[[ url("backend.user/states") ]]'
	);
</script>