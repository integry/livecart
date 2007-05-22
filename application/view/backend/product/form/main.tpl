<fieldset>
    <legend>{t _main_details}</legend>
    
	<p class="checkbox" class="container">
		{checkbox name="isEnabled" class="checkbox" id="product_`$cat`_`$product.ID`_isEnabled" class="checkbox" value="on"}
        <label for="product_{$cat}_{$product.ID}_isEnabled">{t _enabled}</label>
	</p>   
	<p class="required">
		<label for="product_{$cat}_{$product.ID}_name">{t _product_name}:</label>
		<fieldset class="error">
			{textfield name="name" id="product_`$cat`_`$product.ID`_name" class="wide"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_handle">{t _handle}:</label>
		{textfield name="handle" id="product_`$cat`_`$product.ID`_handle"}
	</p>
    <p class="required">
    	<label for="product_{$cat}_{$product.ID}_sku"><a class="acronym">{t _sku_code}<div>{t _what_is_sku}</div></a>:</label>
    	<fieldset class="error">
    		{textfield name="sku" id="product_`$cat`_`$product.ID`_sku" class="product_sku" autocomplete="controller=backend.product field=sku"} 
    		<div class="errorText hidden"></div>
    	</fieldset>			
    </p>
	<p>
		<label for=""></label>
		{checkbox name="autosku" id="product_`$cat`_`$product.ID`_sku_auto" class="checkbox" value="on" onclick="Backend.Product.toggleSkuField(this);"}
		<label for="product_{$cat}_{$product.ID}_sku_auto" class="checkbox">{t _generate_sku}</label>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_shortdes">{t _short_description}:</label>
		<div class="textarea">
			{textarea class="shortDescr" id="product_`$cat`_`$product.ID`_shortdes" name="shortDescription"}
		</div>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_longdes">{t _long_description}:</label>
		<div class="textarea">
			{textarea class="longDescr" id="product_`$cat`_`$product.ID`_longdes" name="longDescription"}
		</div>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_type">{t _product_type}:</label>
		<fieldset class="error">
			{selectfield options=$productTypes name="type" id="product_`$cat`_`$product.ID`_type"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_url">{t _website_address}:</label>
		<fieldset class="error">
			{textfield name="URL" class="wide" id="product_`$cat`_`$product.ID`_url" autocomplete="controller=backend.product field=URL"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_manufacterer">{t _manufacturer}:</label>
		<fieldset class="error">
			{textfield name="manufacturer" class="wide" autocomplete="controller=backend.manufacturer field=manufacturer" id="product_`$cat`_`$product.ID`_manufacterer"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_keywords">{t _keywords}:</label>
		<fieldset class="error">
			{textfield name="keywords" class="wide" id="product_`$cat`_`$product.ID`_keywords" autocomplete="controller=backend.product field=keywords"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>

	<p>
		{checkbox name="isFeatured" class="checkbox" value="on" id="product_`$cat`_`$product.ID`_isfeatured"}
		<label for="product_{$cat}_{$product.ID}_isfeatured">{t _mark_as_featured_product}</label>
	</p>
</fieldset>