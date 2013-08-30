{% extends "layout/frontend.tpl" %}

{% block title %}{t _login}{{% endblock %}
[[ partial('layout/frontend/layout.tpl', ['hideLeft': true]) ]]
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

		{capture assign="return"}{link controller="user"}{/capture}
		[[ partial('user/loginForm.tpl', ['return': return]) ]]
	</div>

	<div class="newCustomer">
		<h2>{t _new_cust}</h2>

		<p class="text-muted">{t _not_registered}</p>

		[[ partial("user/regForm.tpl") ]]
	</div>

	<div class="clear"></div>

{% endblock %}


	<script type="text/javascript">
		Event.observe(window, 'load', function() {$('email').focus()});
	</script>


