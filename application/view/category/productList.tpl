<ul class="productList"> 
{foreach from=$products item=product name="productList"}
	<li class="{if $product.isFeatured}featured{/if}">			
		
		{include file="category/productListItem.tpl"}
			
		{if !$smarty.foreach.productList.last}
            <div style="border-bottom: 1px solid #EEEEEE; margin-top: 15px; margin-bottom: -7px; margin-left: 110px; margin-right: auto;"></div>
        {/if}
			
	</li>
{/foreach}
</ul>