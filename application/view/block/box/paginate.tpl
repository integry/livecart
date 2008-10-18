{if $urls.previous}
	<a class="page previous" href="{$urls.previous}">{t _previous}</a>
{/if}

{foreach $pages as $page}
	{if $last < $page - 1}
		...
	{/if}

	{if $page == $current}
		<span class="page currentPage">{$page}</span>
	{else}
		<a class="page" href="{$urls.$page}">{$page}</a>
	{/if}

	{assign var="last" value=$page}
{/foreach}

{if $urls.next}
	<a class="page next" href="{$urls.next}">{t _next}</a>
{/if}