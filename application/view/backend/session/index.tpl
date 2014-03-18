{% extends "layout/backend.tpl" %}

{% title %}{t _backend_login}{% endblock %}

{% block content %}

	<div id="logoContainer" class="col-lg-3 center-block">

	{# <img src="[[ config('BACKEND_LOGIN_LOGO') ]]" /> #}

	{% if req('failed') %}
		<div class="loginFailed">{t _login_failed}</div>
	{% endif %}

	<my-form action="[[ url("backend/session/doLogin") ]]" method="post" />

		[[ textfld('email', '_email', ['type': 'email']) ]]

		[[ pwdfld('password', '_your_pass') ]]
		<a href="[[ url("user/remindPassword") ]]" class="forgottenPassword">
			{t _remind_password}
		</a>
		
		[[ partial('block/submit.tpl', ['caption': "_login"]) ]]

	</form>
	</div>

{% endblock %}
