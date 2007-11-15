<div id="searchContainer"> 
	{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
	{form action="controller=category" class="quickSearch" handle=$form}
		{selectfield name="id" options=$categories}
		{textfield class="text searchQuery" name="q"}		
		<input type="submit" class="submit" value="Search" />
		<input type="hidden" name="cathandle" value="search" />
	{/form}

	{block CURRENCY}	
	{block LANGUAGE}

	<div class="clear"></div>		
</div>