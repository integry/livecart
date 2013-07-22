<div class="panel inventory {if 'INVENTORY_TRACKING_DOWNLOADABLE'|config}downloadableInventory{/if}">
	<div class="panel-heading">{t _inventory}</div>

	{input name="isUnlimitedStock"}
		{checkbox class="isUnlimitedStock"}
		{label}{tip _unlimited_stock}{/label}
	{/input}

	<div class="stockCount" ng-show="product.isUnlimitedStock == false">
		{input name="stockCount"}
			{label}{tip _items_in_stock}:{/label}
			{textfield class="number"}
		{/input}
	</div>
</div>