{block BREADCRUMB}

<div class="clear"></div>

<div id="header" style="background-color: #EEEEEE;"> 
				
	<fieldset class="container" style="position: relative;">
		
		<div style="float: left;">
            <div style="float: left;">
                <a href="{link}"><img src="image/promo/transparentlogo_small.png" /></a>
               	<span id="storeName">Demo</span>
            </div>
            
    		<form action="{categoryUrl data=$category}" class="quickSearch" style="float: left; padding-left: 30px; padding-top: 15px;">
    		    <input type="text" class="text searchQuery" name="q" value="search" />
    		    <input type="submit" class="submit" value="Go!">
    		</form>
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