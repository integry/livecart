{form class="productDetails" model="product" handle=$productForm method="post" role="product.update"}

	<table class="panelGrid"><tr><td>
	{include file="backend/product/form/main.tpl" productTypes=$productTypes}

	{*
	{if $specFieldList}
		<div class="specFieldContainer">
		{include file="backend/product/form/specFieldList.tpl" product=$product cat=$cat specFieldList=$specFieldList}
		</div>
	{/if}
	*}

	<fieldset>
		<legend>{t _presentation}</legend>

		{input name="isVariationImages"}
			{label}{tip _theme}:{/label}
			{selectfield options=$themes}
		{/input}

		{input name="isVariationImages"}
			{checkbox}
			{label}{tip _show_variation_images}{/label}
		{/input}

		{input name="isAllVariations"}
			{checkbox}
			{label}{tip _allow_all_variations}{/label}
		{/input}
	</fieldset>

	</td><td>

	<div class="productForm ng-class="{ldelim}'intangible': product.type == 1{rdelim}">
		{include file="backend/product/form/inventory.tpl" product=$product cat=$product.categoryID baseCurrency=$baseCurrency form=$productForm}
		{include file="backend/product/form/pricing.tpl" product=$product cat=$product.categoryID baseCurrency=$baseCurrency}
		{include file="backend/product/form/shipping.tpl" product=$product cat=$product.categoryID baseCurrency=$baseCurrency}
	</div>

	</td></tr></table>

	{include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$multiLingualSpecFields}

	{*

	<script type="text/javascript">
		var editor = Backend.Product.Editor.prototype.getInstance({$product.ID}, true, {json array=$path}, {$counters});
		new Backend.ThemePreview($('productThemePreview_{$product.ID}'), $('productTheme_{$product.ID}'));
		var qp=$("quantityPricingViewPort_{$product.ID}");
		qp.style.width=(qp.up("fieldset").getWidth()-4)+"px";
	</script>
	*}
{/form}
