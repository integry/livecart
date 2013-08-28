<div class="multi">
	<p>
		{t _sitemap_ping_complete}
	</p>

	{foreach from=$result key=engine item=status}
	<ul class="siteMapSubmission">
		<li class="{if !$status}submitFail{/if}">[[engine]]</li>
	</ul>
	{/foreach}
</div>