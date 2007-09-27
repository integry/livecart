{loadJs}

{assign var="metaDescription" value=$product.shortDescription_lang}
{assign var="metaKeywords" value=$product.keywords_lang}
{pageTitle}{$product.name_lang}{/pageTitle}

<div class="productIndex productCategory_{$product.Category.ID} product_{$product.ID}">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" style="margin-left: 0;">
    
    <div class="returnToCategory">
        {assign var="lastBreadcrumb" value=$breadCrumb|@end}
        {assign var="lastBreadcrumb" value=$breadCrumb|@prev}
        <a href="{$lastBreadcrumb.url}" class="returnToCategory">{$product.Category.name_lang}</a>
    </div>
        
    <h1>{$product.name_lang}</h1>
    <div class="clear"></div>
    
	<div id="imageContainer" style="float: left; text-align: center;">
		<div id="largeImage" class="{if !$product.DefaultImage.paths.3}missingImage{/if} {if $images|@count > 1}multipleImages{/if}">
			{if $product.DefaultImage.paths.3}
				{img src=$product.DefaultImage.paths.3 alt=$product.DefaultImage.name_lang|escape id="mainImage" style="margin: 20px;"}
			{else}
				{img src="image/missing_large.jpg" alt=$product.DefaultImage.name_lang|escape id="mainImage" style="margin: 20px;"}
			{/if}
		</div>
        {if $images|@count > 1}
			<div id="moreImages">
                {foreach from=$images item="image"}
					{img src=$image.paths.1 id="img_`$image.ID`" alt=$image.name_lang|escape}
				{/foreach}
			</div>
		{/if}
	</div>
    
    <div id="mainInfo" style="clear: right;">

    	{if $product.listAttributes}
    		<div class="specSummary">
    			{foreach from=$product.listAttributes item="attr" name="attr"}
                    {if $attr.values}
                        {foreach from=$attr.values item="value" name="values"}
                            {$value.value_lang}
    						{if !$smarty.foreach.values.last}
    						/
    						{/if}
                        {/foreach}
                    {elseif $attr.value}
                        {$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
                    {elseif $attr.value_lang}
                        {$attr.value_lang}
                    {/if}
                                                
    				{if !$smarty.foreach.attr.last}
    				/
    				{/if}
    			{/foreach}
    		</div>
    		
    		<div style="clear: right;"></div>
    		
    	{/if}    

        <table>
			<tr id="productPrice">
				<td class="param">{t Price}:</td>
				<td class="value price">{$product.formattedPrice.$currency}</td>
			</tr>
			<tr>
				<td colspan="2" class="cartLinks">
					{form action="controller=order action=addToCart id=`$product.ID`" handle=$cartForm}
						{t Quantity}: {selectfield name="count" style="width: auto;" options=$quantity}
						<input type="submit" class="submit" value="{tn Add to Cart}" />							
    					{hidden name="return" value=$catRoute}
					{/form}
				</td>
			</tr>
			<tr>
				<td colspan="2" class="cartLinks addToWishList">
					<a href="{link controller=order action=addToWishList id=$product.ID query="return=`$catRoute`"}">{t Add to Wishlist}</a>			
				</td>
			</tr>

			<tr>
				<td class="param">{t Manufacturer}:</td>
				<td class="value"><a href="{categoryUrl data=$product.Category addFilter=$manufacturerFilter}">{$product.Manufacturer.name}</a></td>
			</tr>
			<tr>
				<td class="param">{t SKU}:</td>
				<td class="value">{$product.sku}</td>
			</tr>

			{if $product.stockCount && 'PRODUCT_DISPLAY_STOCK'|config}
			<tr>
				<td class="param">{t In Stock}:</td>
				<td class="value">{$product.stockCount}</td>
			</tr>
			{/if}

			{if !$product.isDownloadable}
                {if !$product.stockCount && 'PRODUCT_DISPLAY_NO_STOCK'|config}
    			<tr>
    				<td colspan="2" class="noStock"><span>{t _no_stock}</span></td>
    			</tr>
    			{/if}

                {if $product.stockCount && 'PRODUCT_DISPLAY_LOW_STOCK'|config}
    			<tr>
    				<td colspan="2" class="lowStock"><span>{t _low_stock}</span></td>
    			</tr>
    			{/if}
    		{/if}
    			
			{if $product.URL}
            <tr>
                <td colspan="2" class="websiteUrl"><a href="{$product.URL}" target="_blank">{t _product_website}</a></td>            
            </tr>			
            {/if}
            
		</table>	
    </div>
   	
   	<div class="clear"></div>
   
   	{if $product.longDescription_lang}
    <h2>{t Description}</h2>
    <div id="productDescription">
        {$product.longDescription_lang}    
    </div>
	{/if}

    {if $product.attributes}
    <h2>{t Product Specification}</h2>
    <div id="productSpecification">
        <table>
            {foreach from=$product.attributes item="attr" name="attributes"}
                {if $attr.SpecField.isDisplayed && ($attr.values || $attr.value_lang || $attr.value)}
                    {if $prevAttr.SpecField.SpecFieldGroup.ID != $attr.SpecField.SpecFieldGroup.ID}
                        <tr class="specificationGroup">
                            <td colspan="2">{$attr.SpecField.SpecFieldGroup.name_lang}</td>
                        </tr>
                    {/if}
                    <tr {zebra loop="attributes"}>
                        <td>{$attr.SpecField.name_lang}</td>
                        <td>
                            {if $attr.values}
                                <ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
                                    {foreach from=$attr.values item="value"}
                                        <li> {$value.value_lang}</li>
                                    {/foreach}
                                </ul>
                            {elseif $attr.value_lang}
                                {$attr.value_lang}
                            {elseif $attr.value}
                                {$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
                            {/if}
                        </td>
                    </tr>   
                    {assign var="prevAttr" value=$attr}                         
                {/if}
            {/foreach}
        </table>
    </div>
    {/if}
    
    {if $related}
	<h2>{t Recommended Products}</h2>
	<div id="relatedProducts">
		
		{foreach from=$related item=group}
    	   
           {if $group.0.ProductRelationshipGroup.name_lang}
    	       <h3>{$group.0.ProductRelationshipGroup.name_lang}</h3>
    	   {/if}
    	   
           {include file="category/productList.tpl" products=$group}
           
    	{/foreach}
		
	</div>
	{/if}
	
	{if $reviews}
	<h2>{t Customer Reviews}</h2>
    {/if}    
    
</div>

{literal}
<script type="text/javascript">
{/literal}
	var imageData = $H();
	var imageDescr = $H();
	{foreach from=$images item="image"}
		imageData[{$image.ID}] = {json array=$image.paths};
		imageDescr[{$image.ID}] = {json array=$image.title_lang};
	{/foreach}
	new Product.ImageHandler(imageData, imageDescr);
</script>

{include file="layout/frontend/footer.tpl"}

</div>