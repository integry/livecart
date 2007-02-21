<div id="productManagerContainer" class="managerContainer" style="display: none;">
    <a href="#cancelEditing" id="cancel_product_edit">Cancel editing product</a>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="productBasic" class="tab active"><a href="{link       controller=backend.product action=basicData       id=_id_}?categoryID=_categoryID_}">Basic data</a><span> </span></li>
			<li id="productInventory" class="tab inactive"><a href="{link controller=backend.product action=inventory       id=_id_}?categoryID=_categoryID_">Inventory</a><span> </span></li>
			<li id="productImages" class="tab inactive"><a href="{link    controller=backend.productImage action=index      id=_id_}?categoryID=_categoryID_">Images</a><span> </span></li>
			<li id="productRelated" class="tab inactive"><a href="{link   controller=backend.productRelated action=index    id=_id_}?categoryID=_categoryID_">Related products</a><span> </span></li>
			<li id="productOptions" class="tab inactive"><a href="{link   controller=backend.product action=options         id=_id_}?categoryID=_categoryID_">Options</a><span> </span></li>
			<li id="productDiscounts" class="tab inactive"><a href="{link controller=backend.productPrice action=discounts  id=_id_}?categoryID=_categoryID_">Discounts</a><span> </span></li>
			<li id="productFiles" class="tab inactive"><a href="{link     controller=backend.productFile action=index       id=_id_}?categoryID=_categoryID_">Files</a><span> </span></li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>
<script>
    Event.observe($("cancel_product_edit"), "click", function(e) {ldelim}
        Event.stop(e); 
        Backend.Product.Editor.prototype.cancelProductForm();
    {rdelim});
</script>