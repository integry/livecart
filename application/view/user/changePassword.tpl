{% extends "layout/frontend.tpl" %}

{% block title %}{t _change_pass}{{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "passwordMenu"]) ]]
{% block content %}

	{form action="user/doChangePassword" method="POST" handle=$form class="form-horizontal"}

		[[ pwdfld('currentpassword', '_current_pass') ]]

		[[ pwdfld('password', '_enter_new_pass') ]]

		[[ pwdfld('confpassword', '_reenter_new_pass') ]]

		[[ partial('block/submit.tpl', ['caption': "_complete_pass_change", 'cancel': user]) ]]

	{/form}

{% endblock %}
