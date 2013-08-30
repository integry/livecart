{% extends "layout/frontend.tpl" %}

{% block title %}{t _personal_info}{{% endblock %}

[[ partial("user/layout.tpl") ]]
{include file="user/userMenu.tpl" current="personalMenu"}
{% block content %}

	{form class="form-horizontal" action="user/savePersonal" method="POST" handle=$form class="form-horizontal"}

		[[ textfld('firstName', '_your_first_name') ]]

		[[ textfld('lastName', '_your_last_name') ]]

		[[ textfld('companyName', '_company_name') ]]

		{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

		{include file="block/submit.tpl" caption="_save" cancel="user"}
	{/form}

{% endblock %}

