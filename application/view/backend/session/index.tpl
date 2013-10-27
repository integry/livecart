{% extends "layout/backend.tpl" %}

{% title %}{t _backend_login}{% endblock %}

{% block content %}

	<div id="logoContainer">
		{# <img src="[[ config('BACKEND_LOGIN_LOGO') ]]" /> #}
	</div>

	<div id="loginContainer">

	{% if req('failed') %}
		<div class="loginFailed">{t _login_failed}</div>
	{% endif %}

	<form action="[[ url("backend/session/doLogin") ]]" method="post" />

		[[ textfld('email', '_email', ['type': 'email']) ]]

		[[ pwdfld('password', '_your_pass') ]]
		<a href="[[ url("user/remindPassword") ]]" class="forgottenPassword">
			{t _remind_password}
		</a>
		
		[[ partial('block/submit.tpl', ['caption': "_login"]) ]]
		
		<input type="hidden" name="return" value="[[ret]]" />

	</form>
	</div>

{% endblock %}
