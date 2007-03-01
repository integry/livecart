{form handle=$productForm action="controller=backend.product action=save" id="product_`$product.ID`_form" onsubmit="Backend.Product.Editor.prototype.getInstance(`$product.ID`).submitForm(); return false;" method="post"}

   	<div class="pricesSaveConf" style="display: none;">
   		<div class="yellowMessage">
   			<div>
   				Form was successfuly shaved.
   			</div>
   		</div>
   	</div>

    <input type="hidden" name="categoryID" value="{$cat}" />
    
    {include file="backend/product/form/main.tpl" product=$product cat=$cat productTypes=$productTypes}
    {if $specFieldList}
        {include file="backend/product/form/specFieldList.tpl" product=$product cat=$cat specFieldList=$specFieldList}
    {/if}
    {include file="backend/product/form/translations.tpl" product=$product cat=$cat multiLingualSpecFields=$multiLingualSpecFields }
    
    <fieldset>
    	<input type="submit" name="save" class="submit" value="Save"> {t _or} <a class="cancel" href="#">{t _cancel}</a>
    </fieldset>
    
    <script type="text/javascript">
        Backend.Product.Editor.prototype.getInstance({$product.ID});
    </script>
{/form}