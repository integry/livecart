{form handle=$pricingForm action="controller=backend.productPrice action=save" id="product_form_`$product.ID`_`$product.Category.ID`" method="POST" onsubmit="Backend.Product.Prices.prototype.getInstance(this.id).submitForm(); return false; " onreset="Backend.Product.Prices.prototype.getInstance(this.id).resetForm(this);" role="product.update"}
   	<div class="pricesSaveConf" style="display: none;">
   		<div class="yellowMessage">
   			<div>
   				Form was successfuly shaved.
   			</div>
   		</div>
   	</div>
  
    {include file="backend/product/form/inventory.tpl" product=$product cat=$product.Category.ID baseCurrency=$baseCurrency }
    {include file="backend/product/form/pricing.tpl" product=$product cat=$product.Category.ID baseCurrency=$baseCurrency }
    {include file="backend/product/form/shipping.tpl" product=$product cat=$product.Category.ID baseCurrency=$baseCurrency }

	<fieldset class="controls">
		<input type="submit" name="save" class="submit" value="Save">
        {t _or}
        <a class="cancel" href="#">{t _cancel}</a>
	</fieldset>
    <script type="text/javascript">
        Backend.Product.Prices.prototype.getInstance('product_form_{$product.ID}_{$product.Category.ID}', {json array=$product});
    </script>
{/form}