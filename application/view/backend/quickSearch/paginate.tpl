



{if $from > 1 || $to < $count}
	<div class="qsPaginate">
		<span class="qsResults">{t _results}:</span>
		{if $from > 1}
			<a class="qsPrevious" href="javascript:void(0);" onclick="return Backend.QuickSearch.getInstance(this).previous(this, '{$class}');">&lt;&lt;&lt;</a>
		{else}
			<span class="qsPrevious">&lt;&lt;&lt;</span>
		{/if}
		<span class="qsFromCount">{$from}</span>
		{t _paginate_to}
		<span class="qsToCount">{$to}</span>
		{t _paginate_of}
		<span class="qsOfCount">{$count}</span>
		{if $to < $count}
			<a class="qsNext" href="javascript:void(0);" onclick="return Backend.QuickSearch.getInstance(this).next(this, '{$class}');">&gt;&gt;&gt;</a>
		{else}
			<span class="qsNext">&gt;&gt;&gt;</span>
		{/if}
	</div>
{/if}