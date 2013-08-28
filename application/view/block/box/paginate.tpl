<ul class="pagination">
	<li {if !$urls.previous}class="disabled"{/if}><a href="{$urls.previous|default:'#'}">
		<span class="pagination-sign">&laquo;</span>
		<span class="pagination-descr">{t _previous}</span>
	</a></li>

	{foreach $pages as $page}
		{if $last < $page - 1}
			<li class="disabled pagination-space"><a href="#">...</a></li>
		{/if}
	
		<li class="page{if $page == $current} active{/if}"><a href="{$urls.$page}">[[page]]</a></li>
	
		{% set last = $page %}
	{/foreach}

	<li {if !$urls.next}class="disabled"{/if}><a href="{$urls.next|default:'#'}">
		<span class="pagination-descr">{t _next}</span>
		<span class="pagination-sign">&raquo;</span>
	</a></li>

</ul>
