<span {denied role="product.mass"}style="display: none;"{/denied} id="productMass_[[categoryID]]" class="activeGridMass">

	{form action="controller=backend.product action=processMass id=$categoryID" method="POST" handle=$massForm onsubmit="return false;"}

	<input type="hidden" name="filters" value="" />
	<input type="hidden" name="selectedIDs" value="" />
	<input type="hidden" name="isInverse" value="" />

	{t _with_selected}:
	<select name="act" class="select" onchange="Backend.Product.massActionChanged(this);">

		<option value="enable_isEnabled">{t _enable}</option>
		<option value="disable_isEnabled">{t _disable}</option>
		<option value="move">{t _move_to_category}</option>
		<option value="addCat">{t _add_to_category}</option>
		<option value="copy">{t _copy_to_category}</option>
		<option value="delete">{t _delete}</option>

		<option value="manufacturer">{t _set_manufacter}</option>
		<option value="set_keywords">{t _set_keywords}</option>
		<option value="set_URL">{t _set_website_address}</option>
		<option value="addRelated">{t _add_related_product}</option>
		<option value="enable_isFeatured">{t _set_as_featured_product}</option>
		<option value="disable_isFeatured">{t _unset_featured_product}</option>

		<optgroup label="{t _inventory_and_pricing}">
			<option value="inc_price">{t _increase_price}</option>
			<option value="price">{t _set_price} ([[currency]])</option>
			<option value="multi_price">{t _multiply_price}</option>
			<option value="div_price">{t _divide_price}</option>

			<option value="inc_stock">{t _increase_stock}</option>
			<option value="set_stockCount">{t _set_stock}</option>
		</optgroup>

		<optgroup label="{t _shipping_opts}">
			<option value="shippingClass">{t _set_shipping_class}</option>
			<option value="taxClass">{t _set_tax_class}</option>
			<option value="set_shippingWeight">{t _set_shipping_weight}</option>
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

		{% if !empty(attributes) %}
			<optgroup label="{t _set_attributes}">
				{foreach from=$attributes item=attr}
					<option value="set_specField_[[attr.ID]]">[[attr.name()]]</option>
				{/foreach}
			</optgroup>

			<optgroup label="{t _remove_attributes}">
				{foreach from=$attributes item=attr}
					<option value="remove_specField_[[attr.ID]]">[[attr.name()]]</option>
				{/foreach}
			</optgroup>
		{% endif %}
	</select>

	<span id="progressIndicator_specField" class="progressIndicator" style="display: none;"></span>

	<span class="bulkValues" style="display: none;">
		<span class="addRelated">
			{t _enter_sku}: {textfield noFormat=true class="text number" id="massForm_related_`$categoryID`" name="related" autocomplete="controller=backend.product field=sku"}
		</span>
		<span class="move">
			<input type="hidden" name="categoryID" />
		</span>

		<span class="inc_price">
			{textfield noFormat=true id="inc_price_`$categoryID`" class="text number" name="inc_price_value"}%
			{checkbox id="inc_quant_price_`$categoryID`" name="inc_quant_price"}
			<label for="inc_quant_price_[[categoryID]]" style="float: none;">{t _inc_quant_prices}</label>
		</span>

		<span class="multi_price">
			* {textfield noFormat=true id="multi_price_`$categoryID`" class="text number" name="multi_price_value"}
			{checkbox id="multi_quant_price_`$categoryID`" name="multi_quant_price"}
			<label for="multi_quant_price_[[categoryID]]" style="float: none;">{t _inc_quant_prices}</label>
		</span>

		<span class="div_price">
			/ {textfield noFormat=true id="multi_price_`$categoryID`" class="text number" name="div_price_value"}
			{checkbox id="multi_quant_price_`$categoryID`" name="div_quant_price"}
			<label for="multi_quant_price_[[categoryID]]" style="float: none;">{t _inc_quant_prices}</label>
		</span>

		{foreach from=$attributes item=attr}
			<span class="set_specField_[[attr.ID]]"></span>
			{% if $attr.isMultiValue %}
				<span class="remove_specField_[[attr.ID]]"></span>
			{% endif %}
		{/foreach}

		<span class="specFieldValueContainer"></span>

		{textfield noFormat=true id="massForm_inc_stock_`$categoryID`" class="text number" name="inc_stock"}
		{textfield noFormat=true id="massForm_set_stockCount_`$categoryID`" class="text number" name="set_stockCount"}
		{textfield noFormat=true id="massForm_price_`$categoryID`" class="text number" name="price"}
		{textfield noFormat=true id="massForm_set_minimumQuantity_`$categoryID`" class="text number" name="set_minimumQuantity"}
		{textfield noFormat=true id="massForm_shippingSurchargeAmount_`$categoryID`" class="text number" name="set_shippingSurchargeAmount"}
		{textfield noFormat=true id="massForm_shippingWeight_`$categoryID`" class="text number" name="set_shippingWeight"}
		{textfield noFormat=true id="massForm_manufacturer_`$categoryID`" name="manufacturer" class="text" autocomplete="controller=backend.manufacturer field=manufacturer" id="set_manufacturer_`$categoryID`"}
		{textfield noFormat=true id="massForm_set_keywords_`$categoryID`" name="set_keywords" class="text" id="set_keywords_`$categoryID`" autocomplete="controller=backend.product field=keywords"}
		{textfield noFormat=true id="massForm_set_URL_`$categoryID`" name="set_URL" class="text" id="set_url_`$categoryID`" autocomplete="controller=backend.product field=URL"}
		{selectfield noFormat=true id="massForm_theme_`$categoryID`" name="theme" options=$themes}
		{selectfield noFormat=true id="massForm_shippingClass_`$categoryID`" name="shippingClass" options=$shippingClasses}
		{selectfield noFormat=true id="massForm_taxClass_`$categoryID`" name="taxClass" options=$taxClasses}
	</span>

	<input type="submit" value="{t _process}" class="submit" />
	<span class="massIndicator progressIndicator" style="display: none;"></span>

	{/form}

</span>
