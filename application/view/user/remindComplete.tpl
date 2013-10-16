{% extends "layout/frontend.tpl" %}

{% title %}{t _password_sent}{% endblock %}


{% block content %}

	<p>
	   [[ maketext('_has_been_sent_to', [email]) ]]
	</p>

	<p>
	   {t _pass_security_warning}
	</p>

{% endblock %}
