{% extends "layout/frontend.tpl" %}

{% block title %}[[news.title()]]{% endblock %}
{% block left %}{% endblock %}
{% block right %}{% endblock %}

{% block content %}

	<h1>[[news.title()]]</h1>
	<div class="newsDate">[[news.time('d_long')]]</div>

	<div class="newsEntry">
		<p>[[news.text()]]</p>
		<p>[[news.moreText()]]</p>
	</div>

{% endblock %}
