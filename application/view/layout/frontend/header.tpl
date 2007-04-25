{block BREADCRUMB}

<div class="clear" />

<div id="header" style="background-color: #EEEEEE;"> 
				
	<fieldset class="container" style="position: relative;">
		
		<div style="float: left;">
            <div style="float: left;">
                <img src="image/promo/transparentlogo_small.png" />
               	<span id="storeName">Demo</span>
            </div>
            
    		<form action="" class="quickSearch" style="float: left; padding-left: 30px; padding-top: 15px;">
    		    <input type="text" class="text searchQuery" name="search" value="search" />
    		    <input type="submit" class="submit" value="Go!">
    		</form>
        </div>
	
		<div style="float: right;">
        	{* block LANGUAGE *}
			<a href="{link controller=order returnPath=true}">{t Shopping Cart}</a> | 
			<a href="{link controller=checkout returnPath=true}">{t Checkout}</a>
		</div>
	
	</fieldset>
		
</div>