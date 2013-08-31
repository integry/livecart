{% extends "layout/frontend.tpl" %}

{% title %}{t _personal_info}{% endblock %}

[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "personalMenu"]) ]]
{% block content %}

	{form class="form-horizontal" action="user/savePersonal" method="POST" handle=$form class="form-horizontal"}

		[[ textfld('firstName', '_your_first_name') ]]

		[[ textfld('lastName', '_your_last_name') ]]

		[[ textfld('companyName', '_company_name') ]]

		[[ partial('block/eav/fields.tpl', ['item': user, 'filter': "isDisplayed"]) ]]

		[[ partial('block/submit.tpl', ['caption': "_save", 'cancel': "user"]) ]]
	{/form}

{% endblock %}

