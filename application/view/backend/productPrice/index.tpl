<script type="text/javascript" src="/public/javascript/library/tinymce/tiny_mce.js"></script>

<script type="text/javascript" src="/public/firebug/firebug.js"></script>
<script type="text/javascript" src="/public/javascript/library/prototype/prototype.js"></script>
<script type="text/javascript" src="/public/javascript/library/scriptaculous/scriptaculous.js"></script>
<script type="text/javascript" src="/public/javascript/backend/Backend.js"></script>

<script src="/public/javascript/library/livecart.js" type="text/javascript"></script>
<script src="/public/javascript/library/KeyboardEvent.js" type="text/javascript"></script>
<script src="/public/javascript/library/ActiveGrid.js" type="text/javascript"></script>
<script src="/public/javascript/library/ActiveList.js" type="text/javascript"></script>
<script src="/public/javascript/library/form/ActiveForm.js" type="text/javascript"></script>
<script src="/public/javascript/library/form/State.js" type="text/javascript"></script>
<script src="/public/javascript/library/form/Validator.js" type="text/javascript"></script>

<script src="/public/javascript/library/dhtmlxtree/dhtmlXCommon.js" type="text/javascript"></script>
<script src="/public/javascript/library/dhtmlxtree/dhtmlXTree.js" type="text/javascript"></script>
<script src="/public/javascript/library/SectionExpander.js" type="text/javascript"></script>
<script src="/public/javascript/library/rico/rico.js" type="text/javascript"></script>
<script src="/public/javascript/library/TabControl.js" type="text/javascript"></script>
<script src="/public/javascript/library/dhtmlCalendar/calendar.js" type="text/javascript"></script>
<script src="/public/javascript/library/dhtmlCalendar/lang/calendar-en.js" type="text/javascript"></script>
<script src="/public/javascript/library/dhtmlCalendar/calendar-setup.js" type="text/javascript"></script>
<script src="/public/javascript/backend/Category.js" type="text/javascript"></script>

<script src="/public/javascript/backend/SpecField.js" type="text/javascript"></script>
<script src="/public/javascript/backend/Filter.js" type="text/javascript"></script>
<script src="/public/javascript/backend/CategoryImage.js" type="text/javascript"></script>
<script src="/public/javascript/backend/Product.js" type="text/javascript"></script>
<script src="/public/javascript/library/json.js" type="text/javascript"></script>
<script src="/public/javascript/library/Debug.js" type="text/javascript"></script>
<script src="/public/javascript/library/dhtmlHistory/dhtmlHistory.js" type="text/javascript"></script>
<script src="/public/javascript/backend/Customize.js" type="text/javascript"></script>






{form handle=$pricingForm action="controller=backend.productPrice action=save" id="product_form_`$product.ID`_`$product.Category.ID`" method="POST" onsubmit="Backend.Product.Prices.prototype.getInstance(this.id).submitForm(); return false; " onreset="Backend.Product.Prices.prototype.getInstance(this.id).resetForm(this);"}
   	<div class="pricesSaveConf" style="display: none;">
   		<div class="yellowMessage">
   			<div>
   				Form was successfuly shaved.
   			</div>
   		</div>
   	</div>

    {include file="backend/product/form/pricing.tpl" product=$product cat=$product.Category.ID baseCurrency=$baseCurrency }
    {include file="backend/product/form/shipping.tpl" product=$product cat=$product.Category.ID baseCurrency=$baseCurrency }

	<fieldset>
		<input type="submit" name="save" class="submit" value="Save">
        {t _or}
        <a class="cancel" href="#">{t _cancel}</a>
	</fieldset>
    <script type="text/javascript">
        Backend.Product.Prices.prototype.getInstance('product_form_{$product.ID}_{$product.Category.ID}', {json array=$product});
    </script>
{/form}