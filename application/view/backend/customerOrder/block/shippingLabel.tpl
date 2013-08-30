<div style="padding: 1em; border: 1px grey dotted;">
	{% if $address.companyName %}
		<div class="labelCompany" style="font-weight: bold;">
			[[address.companyName]]
		</div>
	{% endif %}

	{% if $address.fullName %}
		<div class="labelName" style="font-weight: bold;">
			[[address.fullName]]
		</div>
	{% endif %}

	<div class="labelAddress1">
		[[address.address1]]
	</div>
	<div class="labelAddress2">
		[[address.address2]]
	</div>

	<div class="labelCity">
		[[address.city]]
	</div>

	<div class="labelState">
		{$address.stateName|default:$address.State.name}{% if $address.postalCode %}, [[address.postalCode]]{% endif %}
	</div>

	<div class="labelCountry">
		[[address.countryName]]
	</div>
</div>
