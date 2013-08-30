{% extends "layout/frontend.tpl" %}

{% block title %}[[news.title_lang]]{{% endblock %}

{% block content %}

	<div class="newsDate">[[news.formatted_time.date_long]]</div>

	<div class="newsEntry">
		<p>[[news.text_lang]]</p>

		{% if $news.moreText_lang %}
			<p>[[news.moreText_lang]]</p>
		{% endif %}
	</div>

{% endblock %}
