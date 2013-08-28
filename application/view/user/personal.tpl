{% extends "layout/frontend.tpl" %}

{% block title %}{t _personal_info}{{% endblock %}

{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="personalMenu"}
{% block content %}

	{form class="form-horizontal" action="controller=user action=savePersonal" method="POST" handle=$form class="form-horizontal"}

		{input name="firstName"}
			{label}{t _your_first_name}:{/label}
			{textfield}
		{/input}

		{input name="lastName"}
			{label}{t _your_last_name}:{/label}
			{textfield}
		{/input}

		{input name="companyName"}
			{label}{t _company_name}:{/label}
			{textfield}
		{/input}

		{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

		{include file="block/submit.tpl" caption="_save" cancel="user"}
	{/form}

{% endblock %}

