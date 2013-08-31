{% extends "layout/frontend.tpl" %}

{% title %}{t _confirming_email}{% endblock %}

{% block content %}

	<p>
	{% if $subscriber.isEnabled %}
		{t _confirm_successful}
	{% else %}
		{t _confirm_unsuccessful}
	{% endif %}
	</p>

{% endblock %}
