{literal}
<script type="text/javascript">
    Backend.Product.Editor.prototype.links = {};
    Backend.Product.Editor.prototype.links.countTabsItems = '{/literal}{link controller=backend.product action=countTabsItems}{literal}';
</script>
{/literal}

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
                
    <span style="float: left; text-align: right;" id="productMass_{$categoryID}">

	    {form action="controller=backend.product action=processMass id=$categoryID" handle=$massForm style="vertical-align: middle;" onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        With selected: 
        <select name="act" class="select" style="width: auto;">
    
            <option value="enable_isEnabled">Enable</option>
            <option value="disable_isEnabled">Disable</option>
            <option value="delete">Delete</option>
                
            <option value="manufacturer">Set manufacturer</option>
            <option value="set_keywords">Set keywords</option>
            <option value="set_URL">Set website address</option>
            <option value="addRelated">Add related product</option>
            <option value="enable_isFeatured">Set as featured product</option>
            <option value="disable_isFeatured">Unset featured product</option>
			                
            <optgroup label="Inventory & Pricing">
                <option value="inc_price">Increase price (percent)</option>
                <option value="inc_stock">Increase stock (count)</option>
    
                <option value="price">Set price ({$currency})</option>
                <option value="set_stockCount">Set stock</option>
            </optgroup>
                        
            <optgroup label="Shipping Options">
                <option value="set_minimumQuantity">Set minimum order quantity</option>
                <option value="set_shippingSurchargeAmount">Set shipping surcharge</option>
                <option value="enable_isFreeShipping">Enable free shipping</option>
                <option value="disable_isFreeShipping">Disable free shipping</option>
                <option value="enable_isBackOrderable">Enable back-ordering</option>
                <option value="disable_isBackOrderable">Disable back-ordering</option>
                <option value="enable_isSeparateShipment">Require separate shipment</option>
                <option value="disable_isSeparateShipment">Do not require separate shipment</option>
            </optgroup>
            
            <optgroup label="Set Attribute Value">
            
            </optgroup>
                            
            <optgroup label="Clear Attribute Value">
            
            </optgroup>
            
        </select>
        
        <span class="bulkValues" style="display: none;">
        	<span class="addRelated">
	            {t Enter product SKU:} {textfield class="text number" name="related" autocomplete="controller=backend.product field=sku"}
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
        <span class="progressIndicator" style="display: none;"></span>
        
        {/form}
        
    </span>
    
    <span style="float: right; text-align: right; position: relative; padding-bottom: 10px;">
		<span id="productCount_{$categoryID}">
			<span class="rangeCount">Listing products %from - %to of %count</span>
			<span class="notFound">No products found</span>
		</span>    
		<br />
		<div style="padding-top: 5px;">
			<a href="#" onclick="Element.show($('productColumnMenu_{$categoryID}')); return false;" style="margin-top: 15px;">{t Columns}</a>
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

<div style="width: 100%; position: relative;">
	<div style="display: none;" class="activeGrid_loadIndicator" id="productLoadIndicator_{$categoryID}">
		<div>
			{t Loading data...}<span class="progressIndicator"></span>
		</div>
	</div>
</div>

<div style="width: 100%;height: 100%;">
<table class="activeGrid productList" id="products_{$categoryID}" style="height: 100%;">
	<thead>
		<tr class="headRow">
	
			<th class="cell_cb"><input type="checkbox" class="checkbox" /></th>
			{foreach from=$displayedColumns item=type key=column name="columns"}
				{if !$smarty.foreach.columns.first}
					<th class="first cellt_{$type} cell_{$column|replace:'.':'_'}">
						<span class="fieldName">{$column}</span>
						{if 'bool' == $type}
				    		<select style="width: auto;" id="filter_{$column}_{$categoryID}">
								<option value="">{tn $column}</option>
								<option value="1">{tn _yes}</option>
								<option value="0">{tn _no}</option>
							</select>					
						{else}
						<input type="text" class="text {$type}" id="filter_{$column}_{$categoryID}" value="{$availableColumns.$column.name|escape}" />
						{/if}
					</th>		
				{/if}
			{/foreach}
		</tr>
	</thead>	
	<tbody>
		{section name="createRows" start=0 loop=15}
			<tr class="{if $smarty.section.createRows.index is even}even{else}odd{/if}">
				<td class="cell_cb"></td>
			{foreach from=$displayedColumns key=column item=type name="columns"}
  			 	{if !$smarty.foreach.columns.first}
					<td class="cellt_{$type} cell_{$column|replace:'.':'_'}"></td>		
				{/if}
			{/foreach}	
			</tr>	
		{/section}
	</tbody>
</table>
</div>

</div>

{literal}
<script type="text/javascript">
	var grid = new ActiveGrid($('products_{/literal}{$categoryID}'), '{link controller=backend.product action=lists}', {$totalCount}, $("productLoadIndicator_{$categoryID}"));

	grid.setDataFormatter(Backend.Product.GridFormatter);
	
	{foreach from=$displayedColumns item=id key=column name="columns"}
		{if !$smarty.foreach.columns.first}
		    new ActiveGridFilter($('filter_{$column}_{$categoryID}'), grid);
		{/if}
	{/foreach}
    
    var massHandler = new Backend.Product.massActionHandler($('productMass_{$categoryID}'), grid);
    massHandler.deleteConfirmMessage = '{t _delete_conf|addslashes}' ;
</script>