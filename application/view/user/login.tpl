{% extends "layout/frontend.tpl" %}

{% title %}{t _login}{% endblock %}
{% block left %}{% endblock %}

{% block content %}

	<div class="returningCustomer">
		<h2>{t _returning}</h2>

		<p>
			{% if !empty(failed) %}
				<div class="errorMsg failed">
					{t _login_failed}
				</div>
			{% else %}
				<p class="text-muted">{t _please_sign_in}</p>
			{% endif %}
		</p>

		[[ partial('user/loginForm.tpl', ['ret': url("user")]) ]]
	</div>

	<div class="newCustomer">
		<h2>{t _new_cust}</h2>

		<p class="text-muted">{t _not_registered}</p>

		[[ partial("user/regForm.tpl") ]]
	</div>

{% endblock %}

