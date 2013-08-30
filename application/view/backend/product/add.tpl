<dialog fullHeight=true class="col-lg-11" cancel="cancel()">
	<dialog-header>{{product.name}}</dialog-header>
	<dialog-body>
		[[ partial("backend/product/basicData.tpl") ]]
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabForm="productDetails" ng-click="save()">{t _save_product}</submit>
	</dialog-footer>
</dialog>

{*
<div class="productForm {% if 1 == $product.type %}intangible{% endif %}">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct([[product.Category.ID]]); return false;">{t _cancel_add_product}</a></li>
		</ul>
	</fieldset>

	{form handle=$productForm action="controller=backend.product action=create id=`$product.ID`" method="POST" onsubmit="Backend.Product.saveForm(this); return false;" onreset="Backend.Product.resetAddForm(this);"}

		<input type="hidden" name="categoryID" value="[[product.Category.ID]]" />

		[[ partial('backend/product/form/main.tpl', ['product': product, 'cat': cat, 'productTypes': productTypes]) ]]
		{% if $specFieldList %}
			<div class="specFieldContainer">
			[[ partial('backend/product/form/specFieldList.tpl', ['product': product, 'cat': cat, 'specFieldList': specFieldList]) ]]
			</div>
		{% endif %}

		[[ partial('backend/product/form/inventory.tpl', ['product': product, 'cat': cat, 'baseCurrency': baseCurrency, 'form': productForm]) ]]
		[[ partial('backend/product/form/pricing.tpl', ['product': product, 'cat': cat, 'baseCurrency': baseCurrency]) ]]
		[[ partial('backend/product/form/shipping.tpl', ['product': product, 'cat': cat, 'baseCurrency': baseCurrency]) ]]

		<fieldset class="productImages">
			<legend>{t _image}</legend>
			<div class="thumbsContainer">
			</div>

			<div class="thumbTemplate" style="display: none; clear:both;">
				<a href="#" class="deleteCross" onclick="{literal}try {$(this).up('div').remove();} catch(e){} return false;{/literal}"></a>
				<div class="fileName" style="float:left;"></div>
				<div class="fileImage">
					<img src="" class="thumb" alt="" />
				</div>
				{hidden name="productImage[]" class="productImageFileName"}
			</div>

			<p class="error">
				<label>{tip _product_image_file}:</label>
				<fieldset class="error uploadContainer">
					{filefield name="upload_productImage" id="product_image_`$cat`_`$product.ID`"}
					<div class="errorText hidden"></div>
				</fieldset>
			</p>

			{filefield name="upload_productImage" class="upload_productImageEmpty" style="display:none;"}
			<input type="hidden" id="fileUploadOptions_[[cat]]_[[product.ID]]" class="fileUploadOptions" value="{link controller="backend.product" action=uploadProductImage query="field=productImage"}" />

			<script type="text/javascript">
				var upload = $('product_image_[[cat]]_[[product.ID]]');
				new LiveCart.FileUpload(upload, $("fileUploadOptions_[[cat]]_[[product.ID]]").value , Backend.Product.previewUploadedImage);
			</script>
		</fieldset>

		[[ partial('backend/product/form/translations.tpl', ['product': product, 'cat': cat, 'multiLingualSpecFields': languageList]) ]]


		<fieldset class="controls">

			<input type="checkbox" name="afterAdding" value="new" style="display: none;" />

			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="adAd_continue" class="submit" value="{t _save_and_continue}" onclick="this.form.elements.namedItem('afterAdding').checked = false;" />
			<input type="submit" name="adAd_new" class="submit" value="{t _save_and_another}"  onclick="this.form.elements.namedItem('afterAdding').checked = true;" />
			{t _or} <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct([[product.Category.ID]]); return false;">{t _cancel}</a>

		</fieldset>

	{/form}

	{literal}
	<script type="text/javascript">
		Backend.Product.initAddForm({/literal}[[product.Category.ID]]{literal});
		Backend.Product.setPath({/literal}[[product.Category.ID]], {json array=$path}{literal})
	</script>
	{/literal}

</div>
*}
