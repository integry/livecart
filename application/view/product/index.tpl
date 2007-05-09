{pageTitle}{$product.name_lang}{/pageTitle}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" style="margin-left: 0;">
    <h1>{$product.name_lang}</h1>
    
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
	{/if}    
    
	<fieldset class="container">
		<div id="imageContainer" style="float: left; text-align: center;">
			<div id="largeImage"{if !$product.DefaultImage.paths.3} class="missingImage"{/if}>
				{if $product.DefaultImage.paths.3}
					<img src="{$product.DefaultImage.paths.3}" id="mainImage" style="margin: 20px;" />
				{else}
					<img src="image/missing_large.jpg" id="mainImage" style="margin: 20px;" />
				{/if}
			</div>
            {if $images|@count > 1}
    			<div id="moreImages">
                    {foreach from=$images item="image"}
    					<img src="{$image.paths.1}" id="img_{$image.ID}" />
    				{/foreach}
    			</div>
    		{/if}
		</div>
	    
	    <div id="mainInfo" style="float: left; clear: right;">
	    	<table>
				<tr id="productPrice">
					<td class="param">{t Price}:</td>
					<td class="value price">{$product.formattedPrice.$currency}</td>
				</tr>
				<tr>
					<td colspan="2" id="cartLinks">
						<p id="addToCart">
							<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}">{t Add to Cart}</a>			
						</p>
						{t _or}
						<p id="addToWishList">
							<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}">{t Add to Wishlist}</a>			
						</p>					
					</td>
				</tr>
				<tr>
					<td class="param">{t Manufacturer}:</td>
					<td class="value">{$product.Manufacturer.name}</td>
				</tr>
				<tr>
					<td class="param">{t SKU}:</td>
					<td class="value">{$product.sku}</td>
				</tr>
				{if $product.stockCount}
				<tr>
					<td class="param">{t In Stock}:</td>
					<td class="value">{$product.stockCount}</td>
				</tr>
				{/if}			
			</table>	
	    </div>
   	</fieldset>
   
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
            {foreach from=$product.attributes item="attr"}
                {if $attr.SpecField.isDisplayed && ($attr.values || $attr.value_lang || $attr.value)}
                    {if $prevAttr.SpecField.SpecFieldGroup.ID != $attr.SpecField.SpecFieldGroup.ID}
                        <tr class="specificationGroup">
                            <td colspan="2">{$attr.SpecField.SpecFieldGroup.name_lang}</td>
                        </tr>
                    {/if}
                    <tr>
                        <td>{$attr.SpecField.name_lang}</td>
                        <td>
                            {if $attr.values}
                                <ul>
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
		
    	{include file="category/productList.tpl" products=$related}
		
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
	{foreach from=$images item="image"}
		imageData[{$image.ID}] = {json array=$image.paths};
	{/foreach}
	new Product.ImageHandler(imageData);
</script>

{include file="layout/frontend/footer.tpl"}