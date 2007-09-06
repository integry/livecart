<div class="productForm {if 1 == $product.type}intangible{/if}">
    <fieldset class="container">
		<ul class="menu">
	        <li class="done"><a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}); return false;">Cancel adding new product</a></li>
	    </ul>
	</fieldset>
        
    {form handle=$productForm action="controller=backend.product action=create id=`$product.ID`" method="POST" onsubmit="Backend.Product.saveForm(this); return false;" onreset="Backend.Product.resetAddForm(this);"}
    	
    	<input type="hidden" name="categoryID" value="{$product.Category.ID}" />
    	
        {include file="backend/product/form/main.tpl" product=$product cat=$cat productTypes=$productTypes}
        
        {if $specFieldList}
            <div class="specFieldContainer">
            {include file="backend/product/form/specFieldList.tpl" product=$product cat=$cat specFieldList=$specFieldList}
            </div>
        {/if}
    	   
        {include file="backend/product/form/inventory.tpl" product=$product cat=$cat baseCurrency=$baseCurrency form=$productForm}
        {include file="backend/product/form/pricing.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}
        {include file="backend/product/form/shipping.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}    
        {include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$languageList}
    
    	<fieldset class="controls">
    		<p>			
    			<label for="">{t _when_the_product_is_added}:</label> 
    			<fieldset class="container">
    				<fieldset class="error">
    					{radio name="afterAdding" id="afAd_new" class="radio" value="new" checked="checked"}<label for="afAd_new" class="radio">{t _add_another_product}</label>
    				</fieldset>
    				<fieldset class="error">
    					{radio name="afterAdding" id="afAd_det" class="radio"}<label for="afAd_det" class="radio"> {t _continue_with_more_details}</label>
    				</fieldset>
    			</fieldset>	
    		</p>	
    	
    		<span class="progressIndicator" style="display: none;"></span>
            <input type="submit" name="save" class="submit" value="Save"> {t _or} <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}); return false;">{t _cancel}</a>
    	</fieldset>
    	
    {/form}
    
    {literal}
    <script type="text/javascript">
    	Backend.Product.initAddForm({/literal}{$product.Category.ID}{literal});
    	Backend.Product.setPath({/literal}{$product.Category.ID}, {json array=$path}{literal})
    </script>
    {/literal}

</div>