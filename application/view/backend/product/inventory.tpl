Inventory

product #{$id} in category #{$categoryID}

{form handle=$inventoryForm action="controller=backend.product action=saveInventory" id="product_inventory_form_`$product.ID`_`$product.Category.ID`" method="POST" onsubmit="Backend.Product.Inventory.prototype.getInstance(this.id).submitForm(); return false; " onreset="Backend.Product.Inventory.prototype.getInstance(this.id).resetForm(this);"}
   	<div class="pricesSaveConf" style="display: none;">
   		<div class="yellowMessage">
   			<div>
   				Form was successfuly shaved.
   			</div>
   		</div>
   	</div>

    {include file="backend/product/form/inventory.tpl" product=$product cat=$product.Category.ID baseCurrency=$baseCurrency }

	<fieldset>
		<input type="submit" name="save" class="submit" value="Save">
        {t _or}
        <a class="cancel" href="#">{t _cancel}</a>
	</fieldset>
    <script type="text/javascript">
        Backend.Product.Prices.prototype.getInstance('product_inventory_form_{$product.ID}_{$product.Category.ID}', {json array=$product});
    </script>
{/form}