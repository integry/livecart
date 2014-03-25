{% if !empty(fields['ADDRESS1']) %}
	[[ textfld(prefix ~ 'address1', '_address') ]]
{% endif %}

{% if !empty(fields['ADDRESS2']) %}
	[[ textfld(prefix ~ 'address2', '') ]]
{% endif %}

{% if !empty(fields['CITY']) %}
	[[ textfld(prefix ~ 'city', '_city') ]]
{% endif %}

{% if !empty(fields['COUNTRY']) %}
	[[ selectfld(prefix ~ 'country', '_country') ]]
{% endif %}

{% if !empty(fields['STATE']) %}
	[[ partial('user/addressFormState.tpl', ['prefix': prefix]) ]]
{% endif %}

{% if !empty(fields['POSTALCODE']) %}
	[[ textfld(prefix ~ 'postalCode', '_postal_code') ]]
{% endif %}
