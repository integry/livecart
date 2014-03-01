<div class="multi">
	<p>
		{t _sitemap_ping_complete}
	</p>

	{% for engine, status in result %}
	<ul class="siteMapSubmission">
		<li class="{% if empty(status) %}submitFail{% endif %}">[[engine]]</li>
	</ul>
	{% endfor %}
</div>