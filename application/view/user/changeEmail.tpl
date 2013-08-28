{% extends "layout/frontend.tpl" %}

{% block title %}{t _change_email}{{% endblock %}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="emailMenu"}
{% block content %}

	{form action="controller=user action=doChangeEmail" method="POST" handle=$form class="form-horizontal"}

		{input}
			{label}{t _current_email}:{/label}
			<div class="controls">[[user.email]]</div>
		{/input}

		{input name="email"}
			{label}{t _new_email}:{/label}
			{textfield}
		{/input}

		{include file="block/submit.tpl" caption="_complete_email_change" cancel="user"}

	{/form}

{% endblock %}
