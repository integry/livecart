<fieldset class="inventory {if 'INVENTORY_TRACKING_DOWNLOADABLE'|config}downloadableInventory{/if}">
	<legend>{t _inventory}</legend>
	<p>
		<label></label>
		{checkbox name="isUnlimitedStock" class="checkbox isUnlimitedStock" id="product_`$cat`_`$product.ID`_isUnlimitedStock"}
		<label class="checkbox" for="product_{$cat}_{$product.ID}_isUnlimitedStock">{t _unlimited_stock}</label>
	</p>

	<div class="stockCount">
		<p {if $form|isRequired:"stockCount"}class="required"{/if}>
			<label for="product_stockCount_{$cat}_{$product.ID}">{t _items_in_stock}:</label>
			<fieldset class="error">
				{textfield name="stockCount" class="number" id="product_stockCount_`$cat`_`$product.ID`"}
				<div class="errorText hidden"></div>
			</fieldset>
		</p>
	</div>
</fieldset>