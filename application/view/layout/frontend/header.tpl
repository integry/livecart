<div class="clear"></div>

<div id="header"> 
				
	<fieldset class="container" style="position: relative;">
		
		<div style="float: left;">
            <div style="float: left;">
                <a href="{link}"><img src="image/promo/logo_small.jpg" /></a>
               	<span id="storeName" style="display: none;">Demo</span>
            </div>
        </div>
	
		<div style="float: right; text-align: center;">

			{* block LANGUAGE *}
        	{block CART}
		</div>

            {block BREADCRUMB}
	
	</fieldset>
		
</div>

<div class="clear"></div>

<div style="border-top: 1px solid #CCCCCC; border-bottom: 1px solid #DDDDDD; background: #F7F7F7; margin-bottom: 10px;"> 
	{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
    {form url=$searchUrl class="quickSearch" handle="generic"}            
        <select>
            <option>All Products</option>
        </select>
        <input type="text" class="text searchQuery" name="q" value="{$searchQuery|escape}" />	    
        <input type="submit" class="submit" value="Search">
	{/form}
	<div class="clear"></div>
</div>


<div class="clear"></div>
<div>

<div class="clear"></div>
</div>