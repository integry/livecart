{% extends "layout/frontend.tpl" %}

{% block title %}{t _reg_confirm}{{% endblock %}

{% block content %}

	{% if $success %}
		<p>{t _reg_confirm_success}</p>
		<p>{t _reg_next_steps}:</p>
		<ul>
			<li><a href="{link controller=checkout action=pay}">{t _reg_next_steps_checkout}</a></li>
			<li><a href="{link controller=user}">{t _reg_next_steps_account}</a></li>
		</ul>
	{% else %}
		<p>{t _reg_confirm_failure}</p>
	{% endif %}

{% endblock %}
