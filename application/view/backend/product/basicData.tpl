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

<div id="productBasic_{$product.ID}Content">

{link controller="backend.product" action=save }
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
        new SectionExpander("product_{$cat}_{$product.ID}_form");
        Backend.Product.Editor.prototype.getInstance({$product.ID});
    </script>
{/form}

</div>