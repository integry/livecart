<fieldset>
    <legend>Main Details</legend>
    
	<p class="checkbox" class="container">
		{checkbox name="isEnabled" class="checkbox" id="product_`$cat`_`$product.ID`_isEnabled" class="checkbox"}
        <label for="product_{$cat}_{$product.ID}_isEnabled"> Enabled (visible)</label>
	</p>   
	<p class="required">
		<label for="product_{$cat}_{$product.ID}_name">Product name:</label>
		<fieldset class="error">
			{textfield name="name" id="product_`$cat`_`$product.ID`_name" class="wide"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_handle">Handle:</label>
		{textfield name="handle" id="product_`$cat`_`$product.ID`_handle"}
	</p>
    <p class="required">
    	<label for="product_{$cat}_{$product.ID}_sku">SKU (code):</label>
    	<fieldset class="error">
    		{textfield name="sku" id="product_`$cat`_`$product.ID`_sku" autocomplete="controller=backend.product field=sku"} 
    		<div class="errorText hidden"></div>
    	</fieldset>			
    </p>
	<p>
		<label for=""></label>
		{checkbox name="autosku" id="product_`$cat`_`$product.ID`_sku_auto" class="checkbox" value="on" onclick="Backend.Product.toggleSkuField(this);"}
		<label for="product_{$cat}_{$product.ID}_sku_auto" class="checkbox">Generate SKU automatically</label>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_shortdes">Short description:</label>
		<div class="textarea">
			{textarea class="shortDescr" id="product_`$cat`_`$product.ID`_shortdes" name="shortDescription"}
		</div>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_longdes">Long description:</label>
		<div class="textarea">
			{textarea class="longDescr" id="product_`$cat`_`$product.ID`_longdes" name="longDescription"}
		</div>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_type">Product Type:</label>
		<fieldset class="error">
			{selectfield options=$productTypes name="type" id="product_`$cat`_`$product.ID`_type"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_url">Website address:</label>
		<fieldset class="error">
			{textfield name="URL" class="wide" id="product_`$cat`_`$product.ID`_url" autocomplete="controller=backend.product field=URL"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_manufacterer">Manufacturer:</label>
		<fieldset class="error">
			{textfield name="manufacturer" class="wide" autocomplete="controller=backend.manufacturer field=manufacturer" id="product_`$cat`_`$product.ID`_manufacterer"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_keywords">Keywords:</label>
		<fieldset class="error">
			{textfield name="keywords" class="wide" id="product_`$cat`_`$product.ID`_keywords" autocomplete="controller=backend.product field=keywords"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>

	<p>
		{checkbox name="isBestseller" class="checkbox" value="on" id="product_`$cat`_`$product.ID`_isbestseller"}
		<label for="product_{$cat}_{$product.ID}_isbestseller">Mark as bestseller</label>
	</p>
</fieldset>