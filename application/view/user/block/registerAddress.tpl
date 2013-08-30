{assign var="fields" value='USER_FIELDS'|config}

{block FORM-NEW-CUSTOMER-TOP}

<div class="registerColumn">

	<h3>{t _your_personal_details}</h3>

	[[ partial("user/block/nameFields.tpl") ]]

	[[ textfld('email', '_your_email') ]]

	[[ checkbox('newsletter', '_newsletter_signup') ]]

	[[ partial("user/block/phoneField.tpl") ]]

	{% if 'PASSWORD_GENERATION'|config != 'PASSWORD_AUTO' %}
		[[ partial("user/block/passwordFields.tpl") ]]
	{% endif %}

	[[ partial('block/eav/fields.tpl', ['item': user, 'filter': "isDisplayed"]) ]]
	[[ partial('block/eav/fields.tpl', ['eavPrefix': prefix]) ]]

</div>

<div class="registerColumn">

	{% if $showHeading && $order.isShippingRequired && !'REQUIRE_SAME_ADDRESS'|config %}
		<h3>{t _billing_address}</h3>
	{% else %}
		<h3>{t _your_address}</h3>
	{% endif %}

	[[ partial("user/block/addressFields.tpl") ]]

</div>