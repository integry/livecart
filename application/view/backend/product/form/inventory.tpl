<fieldset>
	<legend>Inventory</legend>
	<p class="required">
		<label for="product_stockCount_{$cat}_{$product.ID}">Items in stock:</label>
		<fieldset class="error">			
			{textfield name="stockCount" class="number" id="product_stockCount_`$cat`_`$product.ID`"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
</fieldset>