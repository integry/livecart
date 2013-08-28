{% extends "layout/frontend.tpl" %}

{% block title %}{t _change_pass}{{% endblock %}
[[ partial("user/layout.tpl") ]]
{include file="user/userMenu.tpl" current="passwordMenu"}
{% block content %}

	{form action="controller=user action=doChangePassword" method="POST" handle=$form class="form-horizontal"}

		{input name="currentpassword"}
			{label}{t _current_pass}:{/label}
			{textfield type="password"}
		{/input}

		{input name="password"}
			{label}{t _enter_new_pass}:{/label}
			{textfield type="password"}
		{/input}

		{input name="confpassword"}
			{label}{t _reenter_new_pass}:{/label}
			{textfield type="password"}
		{/input}

		{include file="block/submit.tpl" caption="_complete_pass_change" cancel=user}

	{/form}

{% endblock %}
