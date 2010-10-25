<div id="searchContainer">
	<div class="wrapper">
		{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
		{form action="controller=category" class="quickSearch" handle=$form}
			{if 'HIDE_SEARCH_CATS'|config}
				{hidden name="id" value="1"}
			{else}
				{selectfield name="id" options=$categories}
			{/if}
			{textfield class="text searchQuery" name="q"}
			<input type="submit" class="submit" value="{tn _search}" />
			<input type="hidden" name="cathandle" value="search" />
		{/form}

		{block CURRENCY}
		{block LANGUAGE}

		<div class="clear"></div>
	</div>
</div>