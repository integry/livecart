{% extends "layout/frontend.tpl" %}

{% block title %}{t _change_pass}{{% endblock %}
[[ partial("user/layout.tpl") ]]
{include file="user/userMenu.tpl" current="passwordMenu"}
{% block content %}

	{form action="controller=user action=doChangePassword" method="POST" handle=$form class="form-horizontal"}

		[[ pswfld('currentpassword', '_current_pass') ]]

		[[ pswfld('password', '_enter_new_pass') ]]

		[[ pswfld('confpassword', '_reenter_new_pass') ]]

		{include file="block/submit.tpl" caption="_complete_pass_change" cancel=user}

	{/form}

{% endblock %}
