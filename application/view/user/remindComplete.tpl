{% extends "layout/frontend.tpl" %}

{% block title %}{t _password_sent}{{% endblock %}


{% block content %}

	<p>
	   {maketext text=_has_been_sent_to params=$email}
	</p>

	<p>
	   {t _pass_security_warning}
	</p>

{% endblock %}
