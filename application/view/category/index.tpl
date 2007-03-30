{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}</h1>

	<div class="resultStats" style="padding: 10px;">
		Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
	</div>

	<ul class="productList"> 
	{foreach from=$products item=product}
		<li style="margin-bottom: 7px;">			
			
		    <fieldset class="container">
            
                <span class="title">
    				<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
    			</span>
    				
                <p class="image">
                    <a href="{productUrl product=$product filterChainHandle=$filterChainHandle}"><img src="{$product.ProductImage.paths.2}" /></a>
                </p>
                    			
    			<p class="descr">
    				
        			<p class="shortDescr">
                        {$product.shortDescription_lang} {$product.formattedPrice.$currency}
                    </p>
    
        			<p class="spec">
        				{if $product.attributes}
        					{foreach from=$product.attributes item="attr" name="attr"}
        						{if $attr.value_lang}
        							{$attr.valuePrefix_lang}{$attr.value_lang}{$attr.valueSuffix_lang}
        							{if !$smarty.foreach.attr.last}
        							/
        							{/if}
        						{/if}
        					{/foreach}
        				{/if}
        			</p>
            
                </p>
                
                <p class="order">
                    <a href="{link controller=order action=addToCart id=$product.ID returnPath=true}">{t Add to Cart}</a>                
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
		
</div>		
{include file="layout/frontend/footer.tpl"}