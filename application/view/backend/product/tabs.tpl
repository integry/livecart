<div id="productManagerContainer" class="managerContainer" style="display: none;">
    <a href="#cancelEditing" id="cancel_product_edit">Cancel editing product</a>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="productBasic" class="tab active">
				<a href="{link controller=backend.product action=basicData id=_id_}?categoryID=_categoryID_}">{t Basic data}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit"}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productDiscounts" class="tab inactive">
				<a href="{link controller=backend.productPrice action=index id=_id_}?categoryID=_categoryID_">{t Prices &amp; Shipping}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit.pricing}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>

			<li id="productInventory" class="tab inactive">
				<a href="{link controller=backend.product action=inventory id=_id_}?categoryID=_categoryID_">{t Inventory}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit.inventory"}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productImages" class="tab inactive">
				<a href="{link    controller=backend.productImage action=index id=_id_}?categoryID=_categoryID_">{t Images}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit.images"}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productRelated" class="tab inactive">
				<a href="{link   controller=backend.productRelated action=index id=_id_}?categoryID=_categoryID_">{t Related products}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit.related}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productOptions" class="tab inactive">
				<a href="{link   controller=backend.product action=options id=_id_}?categoryID=_categoryID_">{t Options}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit.options}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
						
			<li id="productFiles" class="tab inactive">
				<a href="{link controller=backend.productFile action=index id=_id_}?categoryID=_categoryID_">{t Files}</a>
				<span> </span>
				<a target="_blank" href="{helpUrl help="products.edit.files}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>
<script>
    Event.observe($("cancel_product_edit"), "click", function(e) {ldelim}
        Event.stop(e); 
        Backend.Product.Editor.prototype.showCategoriesContainer();
    {rdelim});
</script>