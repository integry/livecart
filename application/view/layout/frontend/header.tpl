{block BREADCRUMB}

<div class="clear"></div>

<div id="header"> 
				
	<fieldset class="container" style="position: relative;">
		
		<div style="float: left;">
            <div style="float: left;">
                <a href="{link}"><img src="image/promo/transparentlogo_small.png" /></a>
               	<span id="storeName" style="display: none;">Demo</span>
            </div>
            
    		{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
            {form url=$searchUrl class="quickSearch" handle="generic"}            
    		    Search
                <input type="text" class="text searchQuery" name="q" value="{$searchQuery|escape}" />
    		    <input type="submit" class="submit" value="Go!">
    		{/form}
        </div>
	
		<div style="float: right; text-align: center;">

			<div style="margin-bottom: 6px;">
				<a href="{link controller=user action=index}">Your Account</a>
			</div>

			{* block LANGUAGE *}
        	{block CART}
		</div>
	
	</fieldset>
		
</div>