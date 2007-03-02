Related products

product #{$id} in category #{$categoryID}


<script type="text/javascript" src="/public/javascript/library/tinymce/tiny_mce.js"></script>

<script type="text/javascript" src="/public/firebug/firebug.js"></script>
<script type="text/javascript" src="/public/javascript/library/prototype/prototype.js"></script>
<script type="text/javascript" src="/public/javascript/library/scriptaculous/scriptaculous.js"></script>
<script type="text/javascript" src="/public/javascript/backend/Backend.js"></script>

<script type="text/javascript" src="/public/javascript/library/livecart.js" ></script>
<script type="text/javascript" src="/public/javascript/library/KeyboardEvent.js" ></script>
<script type="text/javascript" src="/public/javascript/library/ActiveGrid.js" ></script>
<script type="text/javascript" src="/public/javascript/library/ActiveList.js" ></script>
<script type="text/javascript" src="/public/javascript/library/form/ActiveForm.js" ></script>
<script type="text/javascript" src="/public/javascript/library/form/State.js" ></script>
<script type="text/javascript" src="/public/javascript/library/form/Validator.js" ></script>

<script type="text/javascript" src="/public/javascript/library/dhtmlxtree/dhtmlXCommon.js" ></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlxtree/dhtmlXTree.js" ></script>
<script type="text/javascript" src="/public/javascript/library/SectionExpander.js"></script>
<script type="text/javascript" src="/public/javascript/library/rico/rico.js" ></script>
<script type="text/javascript" src="/public/javascript/library/TabControl.js" ></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlCalendar/calendar.js" ></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlCalendar/lang/calendar-en.js" ></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlCalendar/calendar-setup.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/Category.js" ></script>

<script type="text/javascript" src="/public/javascript/backend/SpecField.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/Filter.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/CategoryImage.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/Product.js" ></script>
<script type="text/javascript" src="/public/javascript/library/json.js" ></script>
<script type="text/javascript" src="/public/javascript/library/Debug.js" ></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlHistory/dhtmlHistory.js" ></script>
<script type="text/javascript" src="/public/javascript/backend/Customize.js" ></script>



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