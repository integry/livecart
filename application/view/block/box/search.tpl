{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
{form action="controller=category" class="form-search navbar-search pull-right" handle=$form}
    <div class="input-append">
		{textfield type="text" class="col-span-2 search-query" name="q"}
		<button type="submit" class="btn">{tn _search}</button>
    </div>

	{if 'HIDE_SEARCH_CATS'|config}
		{hidden name="id" value="1"}
	{else}
		{* selectfield name="id" options=$categories *}
	{/if}

	<input type="hidden" name="cathandle" value="search" />
{/form}
