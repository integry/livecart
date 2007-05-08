<div class="resultStats" style="padding: 10px;">
	Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
</div>

<ul class="productList"> 
{foreach from=$products item=product}
	<li>			
		
	    <fieldset class="container">
        
            <div class="title">
				<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
			</div>
				
            <div class="image">
                <a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">
	    	    {if $product.DefaultImage.paths.2}
					<img src="{$product.DefaultImage.paths.2}" />
		        {else}
		        	<img src="image/missing_small.jpg" />
				{/if}
				</a>
            </div>
                    			
			<div class="descr">
				
  				{if $product.listAttributes}
        			<div class="spec">
    					{foreach from=$product.listAttributes item="attr" name="attr"}
                            {if $attr.values}
                                {foreach from=$attr.values item="value" name="values"}
                                    {$value.value_lang}
        							{if !$smarty.foreach.values.last}
        							/
        							{/if}
                                {/foreach}
                            {elseif $attr.value}
                                {$attr.valuePrefix_lang}{$attr.value}{$attr.valueSuffix_lang}
                            {elseif $attr.value_lang}
                                {$attr.value_lang}
                            {/if}
                                                        
    						{if !$smarty.foreach.attr.last}
    						/
    						{/if}
    					{/foreach}
        			</div>
    			{/if}
                        			
    			<div class="shortDescr">
                    {$product.shortDescription_lang}
                </div>
        
	            <div class="order">
	                <div style="float: left;">
					Our Price: <span class="price">{$product.formattedPrice.$currency}</span> - 
					{if $product.isAvailable}
					<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}">{t Add to Cart}</a>
	                : 
					{/if}
					<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}">{t Add to Wishlist}</a>	
					</div>				                
	            
					<div style="float: right;">
					1/10
					</div>  
				
				</div>      
				        
            </div>
        
        </fieldset>
			
	</li>
{/foreach}
</ul>

{if $count > $perPage}
	<div class="resultPages">
		Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
	</div>
{/if}