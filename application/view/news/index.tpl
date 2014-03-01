{% extends "layout/frontend.tpl" %}

{% title %}{t _news}{% endblock %}

{% block content %}

{% for entry in news %}
	[[ partial('news/newsEntry.tpl', ['entry': entry]) ]]
{% endfor %}

{% endblock %}
