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
		<ul class="tabList tabs">
			<li id="tabProductBasic" class="tab active">
				<a href="{link controller=backend.product action=basicData id=_id_}?categoryID=_categoryID_}">{t _basic_data}</a>
				<span class="tabHelp">products.edit</span>
			</li>

			<li id="tabProductDiscounts" class="tab inactive">
				<a href="{link controller=backend.productPrice action=index id=_id_}?categoryID=_categoryID_">{t _stock_pricing}</a>
				<span class="tabHelp">products.edit.pricing</span>
			</li>

			<li id="tabProductImages" class="tab inactive">
				<a href="{link controller=backend.productImage action=index id=_id_}?categoryID=_categoryID_">{t _images}</a>
				<span class="tabHelp">products.edit.images</span>
			</li>

			<li id="tabProductRelationship" class="tab inactive">
				<a href="{link   controller=backend.productRelationship action=index id=_id_}?categoryID=_categoryID_">{t _related}</a>
				<span class="tabHelp">products.edit.related</span>
			</li>

			<li id="tabProductFiles" class="tab inactive">
				<a href="{link controller=backend.productFile action=index id=_id_}?categoryID=_categoryID_">{t _files}</a>
				<span class="tabHelp">products.edit.files</span>
			</li>

			<li id="tabProductOptions" class="tab inactive" {denied role="option"}style="display:none;"{/denied}>
				<a href="{link controller=backend.productOption action=index id=_id_}?categoryID=_categoryID_">{t _options}</a>
				<span class="tabHelp">products.edit.options</span>
			</li>

			<li id="tabProductReviews" class="tab inactive" {denied role="option"}style="display:none;"{/denied}>
				<a href="{link controller=backend.review action=index id=_id_}">{t _reviews}</a>
				<span class="tabHelp">categories</span>
			</li>

{*
			<li id="tabChildren" class="tab inactive">
				<a href="{link controller=backend.product action=operation id=_id_}?categoryID=_categoryID_">{t _children}</a>
				<span class="tabHelp">products.edit.operations</span>
			</li>
*}

			<li id="tabInfo" class="tab inactive">
				<a href="{link controller=backend.product action=info id=_id_}?categoryID=_categoryID_">{t _info}</a>
				<span class="tabHelp">products.edit.info</span>
			</li>

		</ul>
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