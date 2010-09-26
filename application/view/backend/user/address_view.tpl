{if $address.fullName}
	<p><label class="addressFullName">{$address.fullName}</label></p>
{/if}

{if $address.companyName}
	<p><label class="addressCompanyName">{$address.companyName}</label></p>
{/if}

{if $address.countryName}
	<p><label class="addressCountryName">{$address.countryName}</label></p>
{/if}

{if $address.State.name || $address.stateName}
	<p><label class="addressStateName">{$address.State.name|default:$address.stateName}</label></p>
{/if}

{if $address.city}
	<p><label class="addressCity">{$address.city}</label></p>
{/if}

{if $address.address1}
	<p><label class="addressAddress1">{$address.address1}</label></p>
{/if}

{if $address.address2}
	<p><label class="addressAddress2">{$address.address2}</label></p>
{/if}

{if $address.postalCode}
	<p><label class="addressPostalCode">{$address.postalCode}</label></p>
{/if}

{if $address.phone}
	<p><label class="addressPhone">{$address.phone}</label></p>
{/if}

{include file="backend/eav/view.tpl" item=$address format="row"}
