{literal}
<script type="text/javascript">
    Backend.Product.Editor.prototype.links = {};
    Backend.Product.Editor.prototype.links.countTabsItems = '{/literal}{link controller=backend.product action=countTabsItems}{literal}';
</script>
{/literal}

<div>

<fieldset class="container" {denied role="product.create"}style="display: none"{/denied}>
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
                
    <span style="float: left; text-align: right; {denied role="product.mass"}display: none;{/denied}" id="productMass_{$categoryID}" >

	    {form action="controller=backend.product action=processMass id=$categoryID" handle=$massForm style="vertical-align: middle;" onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        With selected: 
        <select name="act" class="select" style="width: auto;">
    
            <option value="enable_isEnabled">{t _enable}</option>
            <option value="disable_isEnabled">{t _disable}</option>
            <option value="delete">{t _delete}</option>
                
            <option value="manufacturer">{t _set_manufacter}</option>
            <option value="set_keywords">{t _set_keywords}</option>
            <option value="set_URL">{t _set_website_address}</option>
            <option value="addRelated">{t _add_related_product}</option>
            <option value="enable_isFeatured">{t _set_as_featured_product}</option>
            <option value="disable_isFeatured">{t _unset_featured_product}</option>
			                
            <optgroup label="{t _inventory_and_pricing}">
                <option value="inc_price">{t _increase_price}</option>
                <option value="inc_stock">{t _increase_stock}</option>
    
                <option value="price">{t _set_price}</option>
                <option value="set_stockCount">{t _set_stock}</option>
            </optgroup>
                        
            <optgroup label="Shipping Options">
                <option value="set_minimumQuantity">{t _set_minimum_quantity}</option>
                <option value="set_shippingSurchargeAmount">{t _set_shipping_surcharge}</option>
                <option value="enable_isFreeShipping">{t _enable_free_shipping}</option>
                <option value="disable_isFreeShipping">{t _disable_free_shipping}</option>
                <option value="enable_isBackOrderable">{t _enable_back_ordering}</option>
                <option value="disable_isBackOrderable">{t _disable_back_ordering}</option>
                <option value="enable_isSeparateShipment">{t _requires_separate_shippment}</option>
                <option value="disable_isSeparateShipment">{t _do_not_require_separate_shippment}</option>
            </optgroup>
            
        </select>
        
        <span class="bulkValues" style="display: none;">
        	<span class="addRelated">
	            {t _enter_sku:} {textfield class="text number" name="related" autocomplete="controller=backend.product field=sku"}
        	</span>
            {textfield class="text number" name="inc_price"}
            {textfield class="text number" name="inc_stock"}
            {textfield class="text number" name="set_stockCount"}
            {textfield class="text number" name="price"}  
            {textfield class="text number" name="set_minimumQuantity"}
            {textfield class="text number" name="set_shippingSurchargeAmount"}
   			{textfield name="manufacturer" class="text" autocomplete="controller=backend.manufacturer field=manufacturer" id="set_manufacturer_`$categoryID`"}
			{textfield name="set_keywords" class="text" id="set_keywords_`$categoryID`" autocomplete="controller=backend.product field=keywords"}
			{textfield name="set_URL" class="text" id="set_url_`$categoryID`" autocomplete="controller=backend.product field=URL"}
        </span>
        
        <input type="submit" value="{tn _process}" class="submit" />
        <span class="progressIndicator massIndicator" style="display: none;"></span>
        
        {/form}
        
    </span>
    
    <span style="float: right; text-align: right; position: relative; padding-bottom: 10px;">
		<span id="productCount_{$categoryID}">
			<span class="rangeCount">{t _listing_products_from} %from - %to {t _listing_products_count_of} %count</span>
			<span class="notFound">{t _no_products_found}</span>
		</span>    
		<br />
		<div style="padding-top: 5px;">
			<a href="#" onclick="Element.show($('productColumnMenu_{$categoryID}')); return false;" style="margin-top: 15px;">{t _columns}</a>
		</div>
		<div id="productColumnMenu_{$categoryID}" style="left: -250px; position: absolute; z-index: 5; width: auto; display: none;">
  		  <form action="{link controller=backend.product action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.parentNode.parentNode.parentNode.parentNode.parentNode, document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="POST">
			
			<input type="hidden" name="category" value="{$categoryID}" />
			
			<div style="background-color: white; border: 1px solid black; float: right; text-align: center; white-space: nowrap; width: 250px;">
				<div style="padding: 5px; position: static; width: 100%;">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" name="sm" value="{tn Change columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('productColumnMenu_{$categoryID}')); return false;" href="#cancel">{t _cancel}</a>
				</div>
			    <div style="padding: 10px; background-color: white; max-height: 300px; overflow: auto; text-align: left;">
					{foreach from=$availableColumns item=item key=column}
					<p>
						<input type="checkbox" name="col[{$column}]" class="checkbox" id="column_{$column}"{if $displayedColumns.$column}checked="checked"{/if} />
						<label for="column_{$column}" class="checkbox">
							{$item.name}
						</label>
					</p>
					{/foreach}
				</div>
			</div>
		  </form>
		</div>
	</span>
    
</fieldset>

{activeGrid prefix="products" id=$categoryID role="product.mass" controller="backend.product" action="lists" displayedColumns=$displayedColumns availableColumns=$availableColumns totalCount=$totalCount}

</div>

{literal}
<script type="text/javascript">
{/literal}

	grid.setDataFormatter(Backend.Product.GridFormatter);
    
    var massHandler = new Backend.Product.massActionHandler($('productMass_{$categoryID}'), grid);
    massHandler.deleteConfirmMessage = '{t _delete_conf|addslashes}' ;

</script>