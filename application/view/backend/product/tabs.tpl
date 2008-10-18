<div id="productManagerContainer" class="treeManagerContainer maxHeight h--90" style="display: none;">

	<fieldset class="container">
		<ul class="menu doneProduct">
			<li class="done">
				<a href="#cancelEditing" id="cancel_product_edit" class="cancel">{t _done_editing_product}</a>
			</li>
		</ul>

		<a id="productPage" onclick="Backend.Product.Editor.prototype.goToProductPage();" href="{link controller=product action=index id=_id_}" target="_blank" class="external">{t _product_page}</a>

	</fieldset>

	<div class="tabContainer">
		{tabControl id="productTabList"}
			{tab id="tabProductBasic" help="products.edit"}<a href="{link controller=backend.product action=basicData id=_id_}?categoryID=_categoryID_}">{t _basic_data}</a>{/tab}
			{tab id="tabProductBundle" help="categories" hidden=true}<a href="{link controller=backend.productBundle action=index id=_id_}">{t _bundled_products}</a>{/tab}
			{tab id="tabProductDiscounts" help="products.edit.pricing"}<a href="{link controller=backend.productPrice action=index id=_id_}?categoryID=_categoryID_">{t _stock_pricing}</a>{/tab}
			{tab id="tabProductImages" help="products.edit.images"}<a href="{link controller=backend.productImage action=index id=_id_}?categoryID=_categoryID_">{t _images}</a>{/tab}
			{tab id="tabProductVariations" help="products.edit"}<a href="{link controller=backend.childProduct action=index id=_id_}">{t _variations}</a>{/tab}
			{tab id="tabProductRelationship" help="products.edit.related"}<a href="{link controller=backend.productRelationship action=index id=_id_}?categoryID=_categoryID_&type=0">{t _related}</a>{/tab}
			{tab id="tabProductUpsell" help="products.edit.related"}<a href="{link controller=backend.productRelationship action=index id=_id_}?categoryID=_categoryID_&type=1">{t _upsell}</a>{/tab}
			{tab id="tabProductFiles" help="products.edit.files" hidden=true}<a href="{link controller=backend.productFile action=index id=_id_}?categoryID=_categoryID_">{t _files}</a>{/tab}
			{tab id="tabProductOptions" role="option" help="products.edit.options"}<a href="{link controller=backend.productOption action=index id=_id_}?categoryID=_categoryID_">{t _options}</a>{/tab}
			{tab id="tabProductReviews" help="categories" hidden=true}<a href="{link controller=backend.review action=index id=_id_}">{t _reviews}</a>{/tab}
			{tab id="tabProductCategories" help="products.edit.info"}<a href="{link controller=backend.productCategory action=index id=_id_}?categoryID=_categoryID_">{t _product_categories}</a>{/tab}
			{tab id="tabInfo" help="products.edit"}<a href="{link controller=backend.product action=info id=_id_}?categoryID=_categoryID_">{t _info}</a>{/tab}
		{/tabControl}
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>
{literal}
<script type="text/javascript">
	Event.observe($("cancel_product_edit"), "click", function(e) {
		Event.stop(e);
		var product = Backend.Product.Editor.prototype.getInstance(Backend.Product.Editor.prototype.getCurrentProductId(), false);
		product.removeTinyMce();
		product.cancelForm();
		Backend.Product.Editor.prototype.showCategoriesContainer();
		Backend.Breadcrumb.display(Backend.Category.activeCategoryId);
	});
</script>
{/literal}