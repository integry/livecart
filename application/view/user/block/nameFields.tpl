{% if $fields.FIRSTNAME %}
	[[ textfld('`$prefix`firstName', '_your_first_name') ]]
{% endif %}

{% if $fields.LASTNAME %}
	[[ textfld('`$prefix`lastName', '_your_last_name') ]]
{% endif %}

{% if $fields.COMPANYNAME %}
	[[ textfld('`$prefix`companyName', '_company_name') ]]
{% endif %}