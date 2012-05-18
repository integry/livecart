<div class="productQuickImageUploadContainer" id="productImageUploadForm_{$product.ID}" style="display:none;"></div>

{form handle=$productForm action="controller=backend.product action=saveQuickEdit id=`$product.ID`" id="product_`$product.ID`_quick_form onsubmit="return false;" method="post" role="product.update"}
	<input type="hidden" name="categoryID" value="{$cat}" />
	<fieldset class="quickEditOuterContainer">

		<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
			<ul class="menu" id="prodImgMenu_{$product.ID}">
				<li class="prodImageAdd"><a href="javascript:void(0);" id="prodImageAdd_{$product.ID}_add" class="pageMenu" onclick="return Backend.Product.showQuickEditAddImageForm(this,{$product.ID}, '{link controller="backend.productQuickImage id=$product.ID}');">{t _add_image}</a></li>
				<li class="prodImageAddCancel done" style="display: none;"><a href="javascript:void(0);" id="prodImageAdd_{$product.ID}_cancel" onclick="return Backend.Product.hideQuickEditAddImageForm(this, {$product.ID});">{t _cancel_add_image}</a></li>
			</ul>
		</fieldset>

		<p>
			<label></label>
			{checkbox name="isEnabled" class="checkbox isEnabled" id="{uniqid}"}
			<label class="checkbox" for="{uniqid last=true}">{t _enabled}</label>
		</p>

		<p class="required">
			<label for="product_{$cat}_{$product.ID}_name">{t _product_name}:</label>
			<fieldset class="error">
				{textfield name="name" id="product_`$cat`_`$product.ID`_name" class="wide text"}
				<div class="errorText hidden"></div>
			</fieldset>
		</p>
		<p class="required">
			<label for="product_{$cat}_{$product.ID}_sku">{t _sku_code}:</label>
			<fieldset class="error">
				{textfield name="sku" id="product_`$cat`_`$product.ID`_sku" class="product_sku text"}
				<div class="errorText hidden"></div>
			</fieldset>
		</p>

		<p class="required">
		<label for="product_price_{$cat}_{$product.ID}_{$baseCurrency}">{t _pricing}:</label>
		<fieldset class="error">
			{textfield name="price_$baseCurrency" class="text money price" id="product_price_`$cat`_`$product.ID`_`$baseCurrency`"} {$baseCurrency}
			 <div class="errorText hidden"></div>

			{*
			<span class="listPrice">
				{t _list_price}:
				{textfield name="listPrice_$baseCurrency" class="money" id="product_listPrice_`$cat`_`$product.ID`_`$baseCurrency`"}
			</span>
			<a href="" class="menu" style="font-size: smaller; display: none;">{t _set_quant}</a>
			<div class="errorText hidden"></div>
			{include file="backend/product/form/quantityPricing.tpl" currency=$baseCurrency}
			*}
		</fieldset>
	</p>
		<p>
			<label></label>
			{checkbox onchange="var node=$(product_stockCount_`$cat`_`$product.ID`); node.up('div')[this.checked?'hide':'show'](); node.value=0;"
			name="isUnlimitedStock" class="checkbox isUnlimitedStock" id="product_`$cat`_`$product.ID`_isUnlimitedStock"}
			<label class="checkbox" for="product_{$cat}_{$product.ID}_isUnlimitedStock">{t _unlimited_stock}</label>
		</p>
		<div class="stockCount"{if $product.isUnlimitedStock} style="display:none;"{/if}>
			<p {if $productForm|isRequired:"stockCount"}class="required"{/if}>
				<label for="product_stockCount_{$cat}_{$product.ID}">{t _items_in_stock}:</label>
				<fieldset class="error">
					{textfield name="stockCount" class="text number" id="product_stockCount_`$cat`_`$product.ID`"}
					<div class="errorText hidden"></div>
				</fieldset>
			</p>
		</div>
		{include file="block/activeGrid/quickEditControls.tpl"}
	</fieldset>
{/form}
