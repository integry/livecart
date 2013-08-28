<p><label class="addressFullName">[[address.fullName]]</label></p>

<p><label class="addressCompanyName">[[address.companyName]]</label></p>

<p><label class="addressCountryName">[[address.countryName]]</label></p>

<p><label class="addressStateName">{$address.State.name|default:$address.stateName}</label></p>

<p><label class="addressCity">[[address.city]]</label></p>

<p><label class="addressAddress1">[[address.address1]]</label></p>

<p><label class="addressAddress2">[[address.address2]]</label></p>

<p><label class="addressPostalCode">[[address.postalCode]]</label></p>

<p><label class="addressPhone">[[address.phone]]</label></p>

{include file="backend/eav/view.tpl" item=$address format="row"}
