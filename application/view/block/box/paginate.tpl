{if $urls.previous}
	<a class="previous" href="{$urls.previous}">{t _previous}</a>
{/if}

{foreach $pages as $page}
	{if $last < $page - 1}
		<span>...</span>
	{/if}

	{if $page == $current}
		<span class="page currentPage">{$page}</span>
	{else}
		<a class="page" href="{$urls.$page}">{$page}</a>
	{/if}

	{assign var="last" value=$page}
{/foreach}

{if $urls.next}
	<a class="next" href="{$urls.next}">{t _next}</a>
{/if}