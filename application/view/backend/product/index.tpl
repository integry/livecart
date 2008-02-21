{literal}
<script type="text/javascript">
	Backend.Product.Editor.prototype.links = {};
	Backend.Product.Editor.prototype.links.countTabsItems = '{/literal}{link controller=backend.product action=countTabsItems}{literal}';
</script>
{/literal}

<div>

<fieldset class="container" {denied role="product.create"}style="display: none"{/denied}>
	<ul class="menu">
		<li class="addProduct">
			<a href="#" onclick="Backend.Product.showAddForm({$categoryID}, this); return false;">
				{t _add_product}
			</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
	</ul>
</fieldset>

<fieldset class="container activeGridControls">

	<span {denied role="product.mass"}style="display: none;"{/denied} id="productMass_{$categoryID}" class="activeGridMass">

		{form action="controller=backend.product action=processMass id=$categoryID" method="POST" handle=$massForm onsubmit="return false;"}

		<input type="hidden" name="filters" value="" />
		<input type="hidden" name="selectedIDs" value="" />
		<input type="hidden" name="isInverse" value="" />

		{t _with_selected}:
		<select name="act" class="select" onchange="Backend.Product.massActionChanged(this);">

			<option value="enable_isEnabled">{t _enable}</option>
			<option value="disable_isEnabled">{t _disable}</option>
			<option value="move">{t _move_to_category}</option>
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

				<option value="price">{t _set_price} ({$currency})</option>
				<option value="set_stockCount">{t _set_stock}</option>
			</optgroup>

			<optgroup label="{t _shipping_opts}">
				<option value="set_minimumQuantity">{t _set_minimum_quantity}</option>
				<option value="set_shippingSurchargeAmount">{t _set_shipping_surcharge}</option>
				<option value="enable_isFreeShipping">{t _enable_free_shipping}</option>
				<option value="disable_isFreeShipping">{t _disable_free_shipping}</option>
				<option value="enable_isBackOrderable">{t _enable_back_ordering}</option>
				<option value="disable_isBackOrderable">{t _disable_back_ordering}</option>
				<option value="enable_isSeparateShipment">{t _requires_separate_shippment}</option>
				<option value="disable_isSeparateShipment">{t _do_not_require_separate_shippment}</option>
			</optgroup>

			<optgroup label="{t _presentation}">
				<option value="theme">{t _set_theme}</option>
			</optgroup>

		</select>

		<span class="bulkValues" style="display: none;">
			<span class="addRelated">
				{t _enter_sku}: {textfield class="text number" id="massForm_related_`$categoryID`" name="related" autocomplete="controller=backend.product field=sku"}
			</span>
			<span class="move">
				<input type="hidden" name="categoryID" />
			</span>

			{textfield id="massForm_inc_price_`$categoryID`" class="text number" name="inc_price"}
			{textfield id="massForm_inc_stock_`$categoryID`" class="text number" name="inc_stock"}
			{textfield id="massForm_set_stockCount_`$categoryID`" class="text number" name="set_stockCount"}
			{textfield id="massForm_price_`$categoryID`" class="text number" name="price"}
			{textfield id="massForm_set_minimumQuantity_`$categoryID`" class="text number" name="set_minimumQuantity"}
			{textfield id="massForm_shippingSurchargeAmount_`$categoryID`" class="text number" name="set_shippingSurchargeAmount"}
   			{textfield id="massForm_manufacturer_`$categoryID`" name="manufacturer" class="text" autocomplete="controller=backend.manufacturer field=manufacturer" id="set_manufacturer_`$categoryID`"}
			{textfield id="massForm_set_keywords_`$categoryID`" name="set_keywords" class="text" id="set_keywords_`$categoryID`" autocomplete="controller=backend.product field=keywords"}
			{textfield id="massForm_set_URL_`$categoryID`" name="set_URL" class="text" id="set_url_`$categoryID`" autocomplete="controller=backend.product field=URL"}
			{selectfield id="massForm_theme_`$categoryID`" name="theme" options=$themes}
		</span>

		<input type="submit" value="{tn _process}" class="submit" />
		<span class="massIndicator progressIndicator" style="display: none;"></span>

		{/form}

	</span>

	<span class="activeGridItemsCount">
		<span id="productCount_{$categoryID}">
			<span class="rangeCount">{t _listing_products}</span>
			<span class="notFound">{t _no_products_found}</span>
		</span>
	</span>

</fieldset>
{activeGrid
	prefix="products"
	id=$categoryID
	role="product.mass"
	controller="backend.product" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	filters=$filters
	container="tabProducts"
}

</div>

{literal}
<script type="text/javascript">
{/literal}
	Backend.Product.GridFormatter.productUrl = '{backendProductUrl}';
	window.activeGrids['products_{$categoryID}'].setDataFormatter(Backend.Product.GridFormatter);

	var massHandler = new ActiveGrid.MassActionHandler(
						$('productMass_{$categoryID}'),
						window.activeGrids['products_{$categoryID}'],
{literal}
						{
							'onComplete':
								function()
								{
									Backend.Product.resetEditors();

									var parentId = {/literal}{$categoryID}{literal};
									var massForm = $('productMass_' + parentId).down('form');
									parentId = Backend.Category.treeBrowser.getParentId(parentId);

									do
									{
										Backend.Product.reloadGrid(parentId);
										parentId = Backend.Category.treeBrowser.getParentId(parentId);
									}
									while(parentId != 0);

									// reload grid of target category if products were moved
									var movedCat = massForm.elements.namedItem('categoryID');
									if (movedCat.value)
									{
										Backend.Product.reloadGrid(movedCat.value);
									}

									movedCat.value = null;
								}
						}
{/literal}
						);
	massHandler.deleteConfirmMessage = '{t _delete_conf|addslashes}' ;
	massHandler.nothingSelectedMessage = '{t _nothing_selected|addslashes}' ;
{literal}
</script>
{/literal}