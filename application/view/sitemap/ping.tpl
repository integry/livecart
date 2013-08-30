<div class="multi">
	<p>
		{t _sitemap_ping_complete}
	</p>

	{foreach from=$result key=engine item=status}
	<ul class="siteMapSubmission">
		<li class="{% if empty(status) %}submitFail{% endif %}">[[engine]]</li>
	</ul>
	{/foreach}
</div>