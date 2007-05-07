<div class="resultStats" style="padding: 10px;">
	Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
</div>

<ul class="productList"> 
{foreach from=$products item=product}
	<li>			
		
	    <fieldset class="container">
        
            <span class="title">
				<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
			</span>
				
            {if $product.DefaultImage.paths.2}
                <p class="image">
                    <a href="{productUrl product=$product filterChainHandle=$filterChainHandle}"><img src="{$product.DefaultImage.paths.2}" /></a>
                </p>
            {/if}
                    			
			<p class="descr">
				
    			<p class="shortDescr">
                    {$product.shortDescription_lang} {$product.formattedPrice.$currency}
                </p>

    			<p class="spec">
    				{if $product.attributes}
    					{foreach from=$product.attributes item="attr" name="attr"}
    						{if $attr.isDisplayedInList}

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

    						{/if}
    					{/foreach}
    				{/if}
    			</p>
        
            </p>
            
            <p class="order">
                {if $product.isAvailable}
				<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}">{t Add to Cart}</a>
                : 
				{/if}
				<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}">{t Add to Wishlist}</a>					                
            </p>
        
        </fieldset>
			
	</li>
{/foreach}
</ul>

{if $count > $perPage}
	<div class="resultPages">
		Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
	</div>
{/if}