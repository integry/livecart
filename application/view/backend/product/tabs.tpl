<div id="productManagerContainer" class="treeManagerContainer maxHeight h--90" style="display: none;">

	<fieldset class="container">
		<ul class="menu doneProduct">
			<li class="done">
				<a href="#cancelEditing" id="cancel_product_edit" class="cancel">{t _done_editing_product}</a>
			</li>
		</ul>

		<a id="productPage" onclick="Backend.Product.Editor.prototype.goToProductPage();" href="[[ url("product/index/_id_") ]]" target="_blank" class="external">{t _product_page}</a>

	</fieldset>

	<div class="tabContainer">
		{tabControl id="productTabList"}
			{tab id="tabProductBasic" help="products.edit"}<a href="[[ url("backend.product/basicData/_id_") ]]?categoryID=_categoryID_}">{t _basic_data}</a>{/tab}
			{tab id="tabProductBundle" help="categories" hidden=true}<a href="[[ url("backend.productBundle/index/_id_") ]]">{t _bundled_products}</a>{/tab}
			{tab id="tabRecurring" help="categories" hidden=true}<a href="[[ url("backend.recurringProductPeriod/index/_id_") ]]">{t _recurring_plan}</a>{/tab}

			{tab id="tabProductImages" help="products.edit.images"}<a href="[[ url("backend.productImage/index/_id_") ]]?categoryID=_categoryID_">{t _images}</a>{/tab}
			{tab id="tabProductVariations" help="products.edit"}<a href="[[ url("backend.productVariation/index/_id_") ]]">{t _variations}</a>{/tab}
			{tab id="tabProductRelationship" help="products.edit.related"}<a href="[[ url("backend.productRelationship/index/_id_") ]]?categoryID=_categoryID_&type=0">{t _related}</a>{/tab}
			{tab id="tabProductUpsell" help="products.edit.related"}<a href="[[ url("backend.productRelationship/index/_id_") ]]?categoryID=_categoryID_&type=1">{t _upsell}</a>{/tab}
			{tab id="tabProductFiles" help="products.edit.files" hidden=true}<a href="[[ url("backend.productFile/index/_id_") ]]?categoryID=_categoryID_">{t _files}</a>{/tab}
			{tab id="tabProductOptions" role="option" help="products.edit.options"}<a href="[[ url("backend.productOption/index/_id_") ]]?categoryID=_categoryID_">{t _options}</a>{/tab}
			{tab id="tabProductReviews" help="categories" hidden=true}<a href="[[ url("backend.review/index/_id_") ]]">{t _reviews}</a>{/tab}
			{tab id="tabProductCategories" help="products.edit.info"}<a href="[[ url("backend.productCategory/index/_id_") ]]?categoryID=_categoryID_">{t _product_categories}</a>{/tab}
			{tab id="tabInfo" help="products.edit"}<a href="[[ url("backend.product/info/_id_") ]]?categoryID=_categoryID_">{t _info}</a>{/tab}
		{/tabControl}
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>

<script type="text/javascript">
	Event.observe($("cancel_product_edit"), "click", function(e) {
		e.preventDefault();
		var product = Backend.Product.Editor.prototype.getInstance(Backend.Product.Editor.prototype.getCurrentProductId(), false);
		product.removeTinyMce();
		product.cancelForm();
		Backend.Product.Editor.prototype.showCategoriesContainer();
		Backend.Breadcrumb.display(Backend.Category.activeCategoryId);
	});
</script>
