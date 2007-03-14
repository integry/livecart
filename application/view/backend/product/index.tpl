<div>

<fieldset class="container">
	<ul class="menu">
		<li>
			<a href="#" onclick="Backend.Product.showAddForm(this.parentNode.parentNode.parentNode.parentNode, {$categoryID}); return false;">
				Add New Product
			</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
	</ul>  
</fieldset>

<fieldset class="container" style="vertical-align: middle;">
    
    {form action="controller=backend.product action=processMass id=$categoryID" handle=$massForm style="vertical-align: middle;" onsubmit="return false;"}
    
    <input type="hidden" name="filters" value="" />
    <input type="hidden" name="selectedIDs" value="" />
    <input type="hidden" name="isInverse" value="" />
            
    <span id="bookmark" style="margin-top: 15px; float: left;"></span>
    
    <span style="float: right; text-align: right;" id="productMass_{$categoryID}">
        With selected: 
        <select name="action" class="select" style="width: auto;">
    
            <option value="enable_isEnabled">Enable</option>
            <option value="disable_isEnabled">Disable</option>
            <option value="delete">Delete</option>
                
            <option value="manufacturer">Set manufacturer</option>
            <option value="keywords">Set keywords</option>
            <option value="URL">Set website address</option>
                
            <optgroup label="Inventory & Pricing">
                <option value="inc_price">Increase price (percent)</option>
                <option value="inc_stock">Increase stock (count)</option>
    
                <option value="price">Set price ({$currency})</option>
                <option value="stock">Set stock</option>
            </optgroup>
                        
            <optgroup label="Shipping Options">
                <option value="set_minimumQuantity">Set minimum order quantity</option>
                <option value="set_shippingSurchargeAmount">Set shipping surcharge</option>
                <option value="enable_isFreeShipping">Enable free shipping</option>
                <option value="disable_isFreeShipping">Disable free shipping</option>
                <option value="enable_isBackOrderable">Enable back-ordering</option>
                <option value="disable_isBackOrderable">Disable back-ordering</option>
            </optgroup>
            
            <optgroup label="Set Attribute Value">
            
            </optgroup>
                            
            <optgroup label="Clear Attribute Value">
            
            </optgroup>
            
        </select>
        
        <span class="bulkValues" style="display: none;">
            {textfield class="text number" name="inc_price"}
            {textfield class="text number" name="inc_stock"}
            {textfield class="text number" name="stock"}
            {textfield class="text number" name="price"}  
   			{textfield name="manufacturer" class="text" autocomplete="controller=backend.manufacturer field=manufacturer" id="set_manufacturer_`$categoryID`"}
			{textfield name="keywords" class="text" id="set_keywords_`$categoryID`" autocomplete="controller=backend.product field=keywords"}
			{textfield name="URL" class="text" id="set_url_`$categoryID`" autocomplete="controller=backend.product field=URL"}
        </span>
        
        <input type="submit" value="{tn _process}" class="submit" />
        <span class="progressIndicator" style="display: none;"></span>
        
    </span>
    
    {/form}
    
</fieldset>

<div style="width: 98%;">
<table class="productHead" id="products_{$categoryID}_header">
	<tr class="headRow">
		<th class="cell_cb"><input type="checkbox" class="checkbox" /></th>
		<th class="first cell_sku">
			<span class="fieldName">Product.sku</span>
			<input type="text" class="text" id="filter_Product.sku_{$categoryID}" value="{tn SKU}" />
		</th>
		<th class="cell_name">
            <span class="fieldName">Product.name</span>
    		<input type="text" class="text" id="filter_Product.name_{$categoryID}" value="{tn Name}" />                    
        </th>	
		<th class="cell_manuf">
            <span class="fieldName">Manufacturer.name</span>
    		<input type="text" class="text" id="filter_Manufacturer.name_{$categoryID}" value="{tn Manufacturer}" />  
        </th>	
		<th class="cell_price">
            <span class="fieldName">ProductPrice.price</span>Price <small>({$currency})</small>
        </th>
		<th class="cell_stock">
            <span class="fieldName">Product.stockCount</span>
    		<input type="text" class="text" id="filter_Product.stockCount_{$categoryID}" value="{tn In stock}" />   
        </th>	
		<th class="cell_enabled">
            <span class="fieldName">Product.isEnabled</span>{tn Enabled}
        </th>	
	</tr>
</table>
</div>

<div style="width: 98%;">
<table class="activeGrid productList" id="products_{$categoryID}">
	<tbody>
		{include file="backend/product/productList.tpl"}
	</tbody>
</table>
</div>

</div>

{literal}
<script type="text/javascript">
    window.openProduct = function(id, e) 
    {
		Backend.Product.Editor.prototype.setCurrentProductId(id); 
        $('productIndicator_' + id).style.display = '';
		TabControl.prototype.getInstance('productManagerContainer', Backend.Product.Editor.prototype.craftProductUrl, Backend.Product.Editor.prototype.craftProductId); 
        if(Backend.Product.Editor.prototype.hasInstance(id)) 
		{
			Backend.Product.Editor.prototype.getInstance(id);			
		}
//        Event.stop(e);
    }

	var grid = new ActiveGrid($('products_{/literal}{$categoryID}'), '{link controller=backend.product action=lists}', {$totalCount});
    new ActiveGridFilter($('filter_Product.sku_{$categoryID}'), grid);
    new ActiveGridFilter($('filter_Product.name_{$categoryID}'), grid);
    new ActiveGridFilter($('filter_Manufacturer.name_{$categoryID}'), grid);
    new ActiveGridFilter($('filter_Product.stockCount_{$categoryID}'), grid);
    
    new Backend.Product.massActionHandler($('productMass_{$categoryID}'), grid);
</script>