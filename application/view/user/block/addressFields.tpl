{% if $fields.ADDRESS1 %}
	[[ textfld('`$prefix`address1', '_address') ]]
{% endif %}

{% if $fields.ADDRESS2 %}
	{input name="`$prefix`address2"}
		{textfield}
	{/input}
{% endif %}

{% if $fields.CITY %}
	[[ textfld('`$prefix`city', '_city') ]]
{% endif %}

{% if $fields.COUNTRY %}
	{input name="`$prefix`country"}
		{label}{t _country}:{/label}
		{selectfield options=$countries id="{uniqid assign=id_country}"}
		<span class="progressIndicator" style="display: none;"></span>
	{/input}
{% else %}
	{hidden name="`$prefix`country" id="{uniqid assign=id_country}"}
{% endif %}

{% if $fields.STATE %}
	[[ partial('user/addressFormState.tpl', ['prefix': $prefix]) ]]
{% endif %}

{% if $fields.POSTALCODE %}
	[[ textfld('`$prefix`postalCode', '_postal_code') ]]
{% endif %}