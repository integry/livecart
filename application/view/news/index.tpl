{% extends "layout/frontend.tpl" %}

{% block title %}{t _news}{{% endblock %}

{% block content %}

{foreach from=$news item=entry}
	[[ partial('news/newsEntry.tpl', ['entry': entry]) ]]
{/foreach}

{% endblock %}
