<div>
    <p>
        <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}, this.parentNode.parentNode); return false;">Cancel adding new product</a>
    </p>
        
    {form handle=$productForm action="controller=backend.product action=save id=`$product.ID`" method="POST" onsubmit="Backend.Product.saveForm(this); return false;" onreset="Backend.Product.resetAddForm(this);"}
    	
    	<div class="productSaveConf" style="margin-bottom: 10px; display: none;">
    		<div class="yellowMessage">
    			<div>
    				Product was added successfuly. You may now add another product.
    			</div>
    		</div>
    	</div>
    	
    	<input type="hidden" name="categoryID" value="{$product.Category.ID}" />
    	
        {include file="backend/product/form/main.tpl" product=$product cat=$cat productTypes=$productTypes}
        
        {if $specFieldList}
            {include file="backend/product/form/specFieldList.tpl" product=$product cat=$cat specFieldList=$specFieldList}
        {/if}
    	   
        {include file="backend/product/form/inventory.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}
        {include file="backend/product/form/pricing.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}
        {include file="backend/product/form/shipping.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}    
        {include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$languageList}
    
    	<fieldset>
    		<p>			
    			<label for="">When the product is added:</label> 
    			<fieldset class="container">
    				<div style="clear: both;">
    					{radio name="afterAdding" id="afAd_new" class="radio" value="new" checked="checked"}<label for="afAd_new" class="radio"> Add another product</label>
    				</div>
    				<div style="clear: both;">
    					{radio name="afterAdding" id="afAd_det" class="radio"}<label for="afAd_det" class="radio"> Continue with more detailed product configuration (add images, define related products, discounts, etc.)</label>
    				</div>
    			</fieldset>	
    		</p>	
    	
    		<input type="submit" name="save" class="submit" value="Save"> {t _or} <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}, this.parentNode.parentNode.parentNode); return false;">{t _cancel}</a>
    	</fieldset>
    	
    {/form}
    
    {literal}
    <script type="text/javascript">
    	Backend.Product.initAddForm({/literal}{$product.Category.ID}{literal});
    </script>
    {/literal}

</div>