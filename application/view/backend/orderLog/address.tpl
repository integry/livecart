{if $address.firstName !== $otherAddress.firstName}
	<dl class="{if $address.firstName !== $otherAddress.firstName}logValueChanged{/if}">
		<dt>{t _first_name}:</dt>
		<dd>{$address.firstName}&nbsp;</dd>
	</dl>
{/if}

{if $address.lastName !== $otherAddress.lastName}
	<dl class="{if $address.lastName !== $otherAddress.lastName}logValueChanged{/if}">
		<dt>{t _last_name}:</dt>
		<dd>{$address.lastName}&nbsp;</dd>
	</dl>
{/if}

{if $address.companyName !== $otherAddress.companyName}
	<dl class="{if $address.companyName !== $otherAddress.companyName}logValueChanged{/if}">
		<dt>{t _company_name}:</dt>
		<dd>{$address.companyName}&nbsp;</dd>
	</dl>
{/if}

{if $address.address1 !== $otherAddress.address1}
	<dl class="{if $address.address1 !== $otherAddress.address1}logValueChanged{/if}">
		<dt>{t _address_1}:</dt>
		<dd>{$address.address1}&nbsp;</dd>
	</dl>
{/if}

{if $address.address2 !== $otherAddress.address2}
	<dl class="{if $address.address2 !== $otherAddress.address2}logValueChanged{/if}">
		<dt>{t _address_2}:</dt>
		<dd>{$address.address2}&nbsp;</dd>
	</dl>
{/if}

{if $address.city !== $otherAddress.city}
	<dl class="{if $address.city !== $otherAddress.city}logValueChanged{/if}">
		<dt>{t _city}:</dt>
		<dd>{$address.city}&nbsp;</dd>
	</dl>
{/if}

{if $address.postalCode !== $otherAddress.postalCode}
	<dl class="{if $address.postalCode !== $otherAddress.postalCode}logValueChanged{/if}">
		<dt>{t _postal_code}:</dt>
		<dd>{$address.postalCode}&nbsp;</dd>
	</dl>
{/if}

{if $address.countryID !== $otherAddress.countryID}
	<dl class="{if $address.countryID !== $otherAddress.countryID}logValueChanged{/if}">
		<dt>{t _contry_name}:</dt>
		<dd>{$address.countryName}&nbsp;</dd>
	</dl>
{/if}

{if $address.State.ID != $otherAddress.State.ID || $address.stateName != $otherAddress.stateName}
	<dl class="{if $address.State.ID != $otherAddress.State.ID || $address.stateName != $otherAddress.stateName}logValueChanged{/if}">
		<dt>{t _state_name}:</dt>
		<dd>{$address.State.name|default:$address.stateName}&nbsp;</dd>
	</dl>
{/if}