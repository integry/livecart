<fieldset class="inventory {if 'INVENTORY_TRACKING_DOWNLOADABLE'|config}downloadableInventory{/if}">
	<legend>{t _inventory}</legend>

	{input name="isUnlimitedStock"}
		{checkbox class="isUnlimitedStock"}
		{label}{tip _unlimited_stock}{/label}
	{/input}

	<div class="stockCount">
		{input name="stockCount"}
			{label}{tip _items_in_stock}:{/label}
			{textfield class="number"}
		{/input}
	</div>
</fieldset>