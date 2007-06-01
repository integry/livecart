<fieldset>
	<legend>{t _inventory}</legend>
	<p {if $form|isRequired:"stockCount"}class="required"{/if}>
		<label for="product_stockCount_{$cat}_{$product.ID}">{t _items_in_stock}:</label>
		<fieldset class="error">			
			{textfield name="stockCount" class="number" id="product_stockCount_`$cat`_`$product.ID`"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
</fieldset>