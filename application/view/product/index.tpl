{pageTitle}{$product.name_lang}{/pageTitle}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{include file="layout/frontend/rightSide.tpl"}

<div id="content" style="margin-left: 0;">
    <h1>{$product.name_lang}</h1>
    
	<fieldset class="container">
		<div id="imageContainer" style="float: left; text-align: center;">
			<img src="{$product.DefaultImage.paths.3}" id="mainImage" style="margin: 20px;" />
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
				<tr id="price">
					<td class="param">{t Price}:</td>
					<td class="value">{$product.formattedPrice.$currency}</td>
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
				<tr>
					<td class="param">{t In Stock}:</td>
					<td class="value">{$product.stockCount}</td>
				</tr>			
			</table>	
	    </div>
   	</fieldset>
   
    <h2>{t Description}</h2>
    <div id="productDescription">
        {$product.longDescription_lang}    
    </div>

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
    
    <h2>{t Recommended Products}</h2>
	<div id="relatedProducts">
		
    <ul>
	{foreach from=$related item="item"}
		<li>
			<a class="productName" href="{productUrl product=$item}">{$item.name_lang}</a>
			{if $item.DefaultImage}
				<img src="{$item.DefaultImage.paths.2}"/>
			{/if}

		</li>
    {/foreach}	
	</ul>	
	</div>
	
	<h2>{t Customer Reviews}</h2>
    
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