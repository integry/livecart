{% extends "layout/frontend.tpl" %}

{% block title %}{t _confirming_email}{{% endblock %}

{% block content %}

	<p>
	{if $subscriber.isEnabled}
		{t _confirm_successful}
	{else}
		{t _confirm_unsuccessful}
	{/if}
	</p>

{% endblock %}
