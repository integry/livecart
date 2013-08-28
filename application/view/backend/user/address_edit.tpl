{hidden name="`$namePrefix`ID"}

{if !$hideName}
{input name="`$namePrefix`firstName"}
	{label}{t _first_name}:{/label}
	{textfield}
{/input}

{input name="`$namePrefix`lastName"}
	{label}{t _last_name}:{/label}
	{textfield}
{/input}

{input name="`$namePrefix`companyName"}
	{label}{t _company}:{/label}
	{textfield}
{/input}
{/if}

{input name="`$namePrefix`countryID"}
	{label}{t _country}:{/label}
	{selectfield options=$countries class="country" id="`$idPrefix`_countryID"}
{/input}

{input name="`$namePrefix`stateID"}
	{label}{t _state}:{/label}
	{selectfield options=$states id="`$idPrefix`_stateID" class="state"}
	{textfield id="`$idPrefix`_stateName"}
{/input}

{input name="`$namePrefix`city"}
	{label}{t _city}:{/label}
	{textfield}
{/input}

{input name="`$namePrefix`address1"}
	{label}{t _address} 1:{/label}
	{textfield}
{/input}

{input name="`$namePrefix`address2"}
	{label}{t _address} 2:{/label}
	{textfield}
{/input}

{input name="`$namePrefix`postalCode"}
	{label}{t _postal_code}:{/label}
	{textfield}
{/input}

{input name="`$namePrefix`phone"}
	{label}{t _phone}:{/label}
	{textfield}
{/input}

{include file="block/eav/fields.tpl" item=$address fieldList=$specFieldListByOwner.UserAddress[$address.ID]}

<script type="text/javascript">
$('[[idPrefix]]_stateID').stateSwitcher = new Backend.User.StateSwitcher(
		$('[[idPrefix]]_countryID'),
		$('[[idPrefix]]_stateID'),
		$('[[idPrefix]]_stateName'),
		'{link controller="backend.user" action=states}'
	);
</script>