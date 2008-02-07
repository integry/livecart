<div class="productForm {if 1 == $product.type}intangible{/if}">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}); return false;">{t _cancel_add_product}</a></li>
		</ul>
	</fieldset>

	{form handle=$productForm action="controller=backend.product action=create id=`$product.ID`" method="POST" onsubmit="Backend.Product.saveForm(this); return false;" onreset="Backend.Product.resetAddForm(this);"}

		<input type="hidden" name="categoryID" value="{$product.Category.ID}" />

		{include file="backend/product/form/main.tpl" product=$product cat=$cat productTypes=$productTypes}

		{if $specFieldList}
			<div class="specFieldContainer">
			{include file="backend/product/form/specFieldList.tpl" product=$product cat=$cat specFieldList=$specFieldList}
			</div>
		{/if}

		{include file="backend/product/form/inventory.tpl" product=$product cat=$cat baseCurrency=$baseCurrency form=$productForm}
		{include file="backend/product/form/pricing.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}
		{include file="backend/product/form/shipping.tpl" product=$product cat=$cat baseCurrency=$baseCurrency}
		{include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$languageList}

		<fieldset class="controls">

			<input type="checkbox" name="afterAdding" value="new" style="display: none;" />

			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="adAd_continue" class="submit" value="{t _save_and_continue}" onclick="this.form.elements.namedItem('afterAdding').checked = false;" />
			<input type="submit" name="adAd_new" class="submit" value="{t _save_and_another}"  onclick="this.form.elements.namedItem('afterAdding').checked = true;" />
			{t _or} <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}); return false;">{t _cancel}</a>

		</fieldset>

	{/form}

	{literal}
	<script type="text/javascript">
		Backend.Product.initAddForm({/literal}{$product.Category.ID}{literal});
		Backend.Product.setPath({/literal}{$product.Category.ID}, {json array=$path}{literal})
	</script>
	{/literal}

</div>