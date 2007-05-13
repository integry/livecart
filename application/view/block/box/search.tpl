<div style="border-top: 1px solid #CCCCCC; border-bottom: 1px solid #DDDDDD; background: #F7F7F7; margin-bottom: 10px;"> 
	{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
    {form action="controller=category" class="quickSearch" handle=$form style="float: left;"}            
        {selectfield name="id" options=$categories}
        {textfield class="text searchQuery" name="q"}	    
        <input type="submit" class="submit" value="Search">
        <input type="hidden" name="cathandle" value="." />
	{/form}
	<div style="float: right;">
		{block CURRENCY}	
	</div>
	<div class="clear"></div>		
</div>