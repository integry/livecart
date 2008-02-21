{form handle=$productForm action="controller=backend.product action=update id=`$product.ID`" id="product_`$product.ID`_form" onsubmit="Backend.Product.Editor.prototype.getInstance(`$product.ID`, false).submitForm(); return false;" method="post" role="product.update"}

	<input type="hidden" name="categoryID" value="{$cat}" />

	{include file="backend/product/form/main.tpl" product=$product cat=$cat productTypes=$productTypes}
	{if $specFieldList}
		<div class="specFieldContainer">
		{include file="backend/product/form/specFieldList.tpl" product=$product cat=$cat specFieldList=$specFieldList}
		</div>
	{/if}

	<fieldset>
		<legend>{t _presentation}</legend>

		<div style="float: left; width: 550px;">
			<p>
				<label for="productTheme_{$product.ID}">{t _theme}:</label>
				{selectfield name="theme" id="productTheme_`$product.ID`" options=$themes}
			</p>
		</div>

		<div style="float: left;" id="productThemePreview_{$product.ID}"></div>
	</fieldset>

	{include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$multiLingualSpecFields }

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _save}">
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>

	<script type="text/javascript">
		var editor = Backend.Product.Editor.prototype.getInstance({$product.ID}, true, {json array=$path}, {$counters});
		new Backend.ThemePreview($('productThemePreview_{$product.ID}'), $('productTheme_{$product.ID}'));
	</script>
{/form}