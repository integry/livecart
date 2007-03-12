<div id="productManagerContainer" class="managerContainer" style="display: none;">
    
	<fieldset class="container">
		<ul class="menu">
			<li><a href="#cancelEditing" id="cancel_product_edit" class="cancel">{t Cancel editing product}</a></li>
		</ul>
	</fieldset>
	
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="productBasic" class="tab active">
				<a href="{link controller=backend.product action=basicData id=_id_}?categoryID=_categoryID_}">{t Basic data}</a>
				<a target="_blank" href="{helpUrl help="products.edit"}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productDiscounts" class="tab inactive">
				<a href="{link controller=backend.productPrice action=index id=_id_}?categoryID=_categoryID_">{t Prices &amp; Shipping}</a>
				<a target="_blank" href="{helpUrl help="products.edit.pricing}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>

			<li id="productInventory" class="tab inactive">
				<a href="{link controller=backend.product action=inventory id=_id_}?categoryID=_categoryID_">{t Inventory}</a>
				<a target="_blank" href="{helpUrl help="products.edit.inventory"}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productImages" class="tab inactive">
				<a href="{link    controller=backend.productImage action=index id=_id_}?categoryID=_categoryID_">{t Images}</a>
				<a target="_blank" href="{helpUrl help="products.edit.images"}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productRelated" class="tab inactive">
				<a href="{link   controller=backend.productRelated action=index id=_id_}?categoryID=_categoryID_">{t Related products}</a>
				<a target="_blank" href="{helpUrl help="products.edit.related}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
			
			<li id="productOptions" class="tab inactive">
				<a href="{link   controller=backend.product action=options id=_id_}?categoryID=_categoryID_">{t Options}</a>
				<a target="_blank" href="{helpUrl help="products.edit.options}"><img src="image/silk/help.png" class="tabHelp" /></a>
			</li>
						
			<li id="productFiles" class="tab inactive">
				<a href="{link controller=backend.productFile action=index id=_id_}?categoryID=_categoryID_">{t Files}</a>
				<a target="_blank" href="{helpUrl help="products.edit.files}"><img src="image/silk/help.png" class="tabHelp" /></a>
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
        
        var textareas = product.nodes.parent.getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
			tinyMCE.execCommand('mceRemoveControl', true, textareas[k].id);
		}
        
        product.cancelForm();
        SectionExpander.prototype.unexpand(product.nodes.parent);
        Backend.Product.Editor.prototype.showCategoriesContainer();
    });
</script>
{/literal}