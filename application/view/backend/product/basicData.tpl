{form class="productDetails" model="product" handle=$productForm method="post" role="product.update"}

	<table class="panelGrid"><tr><td>
	{include file="backend/product/form/main.tpl" productTypes=$productTypes}

	<ng-include src="getSpecFieldTemplate(product)" />

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
		new Backend.ThemePreview($('productThemePreview_[[product.ID]]'), $('productTheme_[[product.ID]]'));
	</script>
	*}
{/form}
