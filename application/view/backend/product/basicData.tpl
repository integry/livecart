[[ form('', ['ng-submit': 'save()', 'ng-init': ';']) ]] >
	[[ partial('backend/product/form/main.tpl') ]]
</form>

{#
<div class="productForm ng-class="{ldelim}'intangible': product.type == 1{rdelim}">
	[[ partial('backend/product/form/inventory.tpl', ['product': product, 'cat': product.categoryID, 'baseCurrency': baseCurrency, 'form': productForm]) ]]
	[[ partial('backend/product/form/pricing.tpl', ['product': product, 'cat': product.categoryID, 'baseCurrency': baseCurrency]) ]]
	[[ partial('backend/product/form/shipping.tpl', ['product': product, 'cat': product.categoryID, 'baseCurrency': baseCurrency]) ]]
</div>
#}

{#
[[ partial('backend/product/form/translations.tpl', ['product': product, 'cat': cat, 'multiLingualSpecFields': multiLingualSpecFields]) ]]
#}

