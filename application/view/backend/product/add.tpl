<div>
    <p>
        <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}, this.parentNode); return false;">Cancel adding new product</a>
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
    	
    	<fieldset>
    		<legend>Inventory</legend>
    		<p class="required">
    			<label for="stock_addproduct_{$cat}">Items in stock:</label>
    			<fieldset class="error">			
    				{textfield name="stockCount" class="number" id="stock_addproduct_`$cat`"}
    				<div class="errorText hidden"></div>
    			</fieldset>
    		</p>
    	</fieldset>
    
    	<fieldset>
    		<legend>Pricing</legend>
    		<p class="required">
    			<label for="pricebase_addproduct_{$cat}">Price:</label>
    			<fieldset class="error">			
    				{textfield name="price_$baseCurrency" class="money" id="pricebase_addproduct_`$cat`"} {$baseCurrency}
    				<div class="errorText hidden"></div>
    			</fieldset>
    		</p>
    		{foreach from=$otherCurrencies item="currency"}
    		<p>
    			<label for="pricebase_addproduct_{$currency}_{$cat}">Price:</label>
    			<fieldset class="error">				
    				{textfield name="price_$currency" class="money" id="pricebase_addproduct_`$currency`_`$cat`"} {$currency}
    				<div class="errorText hidden"></div>
    			</fieldset>
    		</p>		
    		{/foreach}
    	</fieldset>
    
    	<fieldset>
    		<legend>Shipping</legend>
    
    		<p style="color:red;">
    			<label>Shipping Weight:</label>
    			<fieldset class="error">				
    				
    				{textfield name="shippingHiUnit" onkeyup="Backend.Product.updateShippingWeight(this);" class="number"} <span class="shippingUnit_hi">kg</span>
    				{textfield name="shippingLoUnit" onkeyup="Backend.Product.updateShippingWeight(this);" class="number"} <span class="shippingUnit_lo">g</span>
    				
    				<span class="unitSwitch">
    					<span class="unitDef english_title">Switch to English units</span>
    					<span class="unitDef metric_title">Switch to Metric units</span>
    					<span class="unitDef english_hi">kg</span>
    					<span class="unitDef english_lo">g</span>
    					<span class="unitDef metric_hi">pounds</span>
    					<span class="unitDef metric_lo">ounces</span>
    															
    					<a href="#" onclick="Backend.Product.switchUnitTypes(this); return false;">Switch to English units</a>
    				</span>
    				
    				{hidden name="shippingWeight"}
    				{hidden name="unitsType"}
    				
    				<div class="errorText hidden"></div>
    			</fieldset>
    		</p>
    		<p>
    			<label for="minq_addproduct_{$cat}">Minimum Order Quantity:</label>
    			<fieldset class="error">					
    				{textfield name="minimumQuantity" id="minq_addproduct_`$cat`" class="number" value="0"}
    				<div class="errorText hidden"></div>
    			</fieldset>
    		</p>
    		<p>
    			<label for="surch_addproduct_`$cat`">Shipping Surcharge:</label>
    			<fieldset class="error">	
    				{textfield name="shippingSurcharge" id="surch_addproduct_`$cat`" class="number"} {$baseCurrency}
    				<div class="errorText hidden"></div>
    			</fieldset>
    		</p>
    		<p>			
    			<label for=""></label> 
    			{checkbox name="isSeparateShipment" class="checkbox" id="issep_addproduct_`$cat`" value="on"}
    			<label for="issep_addproduct_{$cat}" class="checkbox"> Requires separate shipment</label>
    		</p>
    		<p>			
    			<label for=""></label> 
    			{checkbox name="isFreeShipping" class="checkbox" id="isfree_addproduct_`$cat`" value="on"}
    			<label class="checkbox" for="isfree_addproduct_{$cat}"> Qualifies for free shipping</label>
    		</p>
    		<p class="checkbox">			
    			{checkbox name="isBackorderable" class="checkbox" value="on"}<label for="isBackorderable"> Allow back-ordering</label>
    		</p>
    	</fieldset>
    
    
        {include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$languageList }
    
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