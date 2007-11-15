<p>
	<label>{t _name}</label>
	<label class="addressFullName">{$address.fullName}</label>	
</p>	

<p>
	<label>{t _company}</label>	
	<label class="addressCompanyName">{$address.companyName}</label>
</p>

<p>
	<label>{t _country}</label>	
	<label class="addressCountryName">{$address.countryName}</label>
</p>

<p>
	<label>{t _state}</label>	
	<label class="addressStateName">{$address.State.name|default:$address.stateName}</label>
</p>	

<p>
	<label>{t _city}</label>	
	<label class="addressCity">{$address.city}</label>
</p>

<p>
	<label>{t _address} 1</label>	
	<label class="addressAddress1">{$address.address1}</label>
</p>	

<p>
	<label>{t _address} 2</label>	
	<label class="addressAddress2">{$address.address2}</label>
</p>	

<p>
	<label>{t _postal_code}</label>	
	<label class="addressPostalCode">{$address.postalCode}</label>
</p>

<p>
	<label>{t _phone}</label>	
	<label class="addressPhone">{$address.phone}</label>
</p>