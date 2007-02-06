{includeCSS file="backend/Product.css"}
{includeJS file="library/form/Validator.js"}
{includeJS file="library/form/ActiveForm.js"}
{includeJS file="backend/Product.js"}

{include file="layout/dev/head.tpl"}

{defun name="specFieldFactory" field="field" language="language"}
	
	{if !$language}
		{assign var="fieldName" value=$field.fieldName}
	{else}
		{assign var="fieldName" value="`$field.fieldName`_`$language`"}
	{/if}
	
	{if $field.type == 1 || $field.type == 5}
		{if $field.isMultiValue}		
			<fieldset class="container multiValueSelect">
			{foreach from=$field.values key="id" item="value"}
				{if '' != $id}
					<p>
						{checkbox name="specItem_`$id`" class="checkbox" value="on"}<label class="checkbox" for="specItem_{$id}"> {$value}</label>
					</p>
				{/if}
			{/foreach}

			<p class="selectMenu">
				<a href="#" onclick="Backend.Product.multiValueSelect(this, true); return false;">Select All</a> | <a href="#" onclick="Backend.Product.multiValueSelect(this, false);  return false;">Deselect All</a>
			</p>

			</fieldset>
		{else}
			{selectfield id=$fieldName name=$fieldName options=$field.values class="select"}		
		{/if}

	{elseif $field.type == 2}
		{textfield id=$fieldName name=$fieldName class="text numeric"}

	{elseif $field.type == 3}
		{textfield id=$fieldName name=$fieldName class="text"}

	{elseif $field.type == 4}
		<div class="textarea">
			{textarea id=$fieldName name=$fieldName}
		</div>

	{elseif $field.type == 6}
		{calendar id=$fieldName name=$fieldName}

	{/if}
{/defun}

{form handle=$productForm action="controller=backend.product action=save id=`$product.ID`" method="POST"}
	
	<input type="hidden" name="categoryID" value="{$product.Category.ID}" />
	
	<fieldset>
		<legend>Main product information</legend>

		<p>			
			<label for=""></label> {checkbox name="isEnabled" class="checkbox" value="on"}<label for="isEnabled"> Enabled (visible)</label>
		</p>

		<p class="required">
			<label for="name">Product name:</label>
			<fieldset class="error">
				{textfield name="name" class="wide" onkeyup="Backend.Product.generateHandle(this);"}
				{error for="name"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p class="required">
			<label for="sku">SKU (code):</label>
			<fieldset class="error">
				{textfield name="sku" autocomplete="controller=backend.product field=sku"} 
				{error for="sku"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p>
			<label for=""></label>
			{checkbox name="autosku" class="checkbox" value="on"}
			<label for="autosku" class="checkbox">Generate SKU automatically</label>
		</p>

		<p>
			<label for="handle">Handle:</label>
			{textfield name="handle"}
		</p>

		<p>
			<label for="shortDescription">Short description:</label>
			<div class="textarea">
				{textarea class="shortDescr" name="shortDescription"}
			</div>
		</p>

		<p>
			<label for="longDescription">Long description:</label>
			<div class="textarea">
				{textarea class="longDescr" name="longDescription"}
			</div>
		</p>

		<p>
			<label for="type">Product Type:</label>
			<fieldset class="error">
				{selectfield options=$productTypes name="type"}
				{error for="type"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p>
			<label for="URL">Website address:</label>
			<fieldset class="error">
				{textfield name="URL" class="wide"}
				{error for="URL"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p>
			<label for="manufacturer">Manufacturer:</label>
			<fieldset class="error">
				{textfield name="manufacturer" class="wide" autocomplete="controller=backend.manufacturer field=manufacturer"}
				{error for="manufacturer"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p>
			<label for="keywords">Keywords:</label>
			<fieldset class="error">
				{textfield name="keywords" class="wide"}
				{error for="keywords"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p>			
			<label for=""></label> {checkbox name="isBestseller" class="checkbox" value="on"}<label for="isBestseller"> Mark as bestseller</label>
		</p>
		<p>

	</fieldset>

	<fieldset class="specField">
		<legend>Product Specification</legend>
		{foreach from=$specFieldList key=groupID item=fieldList}
		
			{if $groupID}
				<fieldset>
					<legend>{$fieldList.0.SpecFieldGroup.name_lang}</legend>
			{/if}
			
			{foreach from=$fieldList item=field}
			<p{if $field.isRequired} class="required"{/if}>		
				<label>{$field.name_lang}:</label>				
				<fieldset class="error">
					{fun name="specFieldFactory" field=$field}			
					{error for=$field.fieldName}<div class="errorText">{$msg}</div>{/error}
				</fieldset>			
			</p>
			{/foreach}
		
			{if $groupID}
				</fieldset>
			{/if}

		{/foreach}		
	</fieldset>
	
	<fieldset>
		<legend>Inventory</legend>
		<p class="required">
			<label>Items in stock:</label>
			<fieldset class="error">			
				{textfield name="stockCount" class="number"}
				{error for="stockCount"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>
		</p>
	</fieldset>

	<fieldset>
		<legend>Pricing</legend>
		<p class="required">
			<label>Price:</label>
			<fieldset class="error">			
				{textfield name="price_$baseCurrency" class="money"} {$baseCurrency}
				{error for="price_$baseCurrency"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>
		</p>
		{foreach from=$otherCurrencies item="currency"}
		<p>
			<label>Price:</label>
			<fieldset class="error">				
				{textfield name="price_$currency" class="money"} {$currency}
				{error for="price_$currency"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>
		</p>		
		{/foreach}
	</fieldset>

	<fieldset>
		<legend>Shipping</legend>

		<p style="color:red;">
			<label>Shipping Weight:</label>
			<fieldset class="error">				
				
				{textfield name="shippingHiUnit" onkeyup="Backend.Product.updateShippingWeight(this);" class="number"} <span class="shippingUnit_hi">kg</span>
				{textfield name="shippingLoUnit" onkeyup="Backend.Product.updateShippingWeight(this);" class="number"} <span class="shippingUnit_lo">g</span>
				
				<span class="unitSwitch">
					<span class="unitDef english_title">Switch to English units</span>
					<span class="unitDef metric_title">Switch to Metric units</span>
					<span class="unitDef english_hi">kg</span>
					<span class="unitDef english_lo">g</span>
					<span class="unitDef metric_hi">pounds</span>
					<span class="unitDef metric_lo">ounces</span>
															
					<a href="#" onclick="Backend.Product.switchUnitTypes(this); return false;">Switch to English units</a>
				</span>
				
				{hidden name="shippingWeight"}
				{hidden name="unitsType"}
				
				{error for="shippingWeight"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>
		</p>

		<p>
			<label>Minimum Order Quantity:</label>
			<fieldset class="error">					
				{textfield name="minimumQuantity" class="number" value="0"}
				{error for="minimumQuantity"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>
		</p>

		<p>
			<label>Shipping Surcharge:</label>
			<fieldset class="error">	
				{textfield name="shippingSurcharge" class="number"} {$baseCurrency}
				{error for="shippingSurcharge"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>
		</p>

		<p>			
			<label for=""></label> {checkbox name="isSeparateShipment" class="checkbox" value="on"}<label for="isSeparateShipment" class="checkbox"> Requires separate shipment</label>
		</p>

		<p>			
			<label for=""></label> {checkbox name="isFreeShipping" class="checkbox" value="on"}<label class="checkbox" for="isFreeShipping"> Qualifies for free shipping</label>
		</p>

		<p>			
			<label for=""></label> {checkbox name="isBackorderable" class="checkbox" value="on"}<label for="isBackorderable"> Allow back-ordering</label>
		</p>

	</fieldset>

	{foreach from=$languageList key=lang item=langName}
	<fieldset class="expandingSection">
		<legend>Translate to: {$langName}</legend>
		<div class="expandingSectionContent">
			<p>
				<label>Product name:</label>
				{textfield name="name_$lang" class="wide"}
			</p>
			<p>
				<label>Short description:</label>
				<div class="textarea">
					{textarea class="shortDescr" name="shortDescription_$lang"}
				</div>
			</p>
			<p>
				<label>Long description:</label>
				<div class="textarea">
					{textarea class="longDescr" name="longDescription_$lang"}
				</div>
			</p>
			
			{if $multiLingualSpecFields}
			<fieldset>
				<legend>Specification Attributes</legend>
				{foreach from=$multiLingualSpecFields item="field"}
					<p>		
						<label for="{$field.fieldName}_{$lang}">{$field.name_lang}:</label>				
						{fun name="specFieldFactory" field=$field language=$lang}			
					</p>
				{/foreach}
			</fieldset>
			{/if}
			
		</div>
	</fieldset>
	{/foreach}

	<fieldset>

		<p>			
			<label for="">When the product is added:</label> 
			<fieldset class="container">
				<div style="clear: both;">
					{radio name="afterAdding" id="afAd_det" class="radio"}<label for="afAd_det" class="radio"> Continue with more detailed product configuration (add images, define related products, discounts, etc.)</label>
				</div>
				<div style="clear: both;">
					{radio name="afterAdding" id="afAd_new" class="radio" value="new"}<label for="afAd_new" class="radio"> Add another product</label>
				</div>
			</fieldset>	
		</p>	
	
		<input type="submit" name="save" class="submit" value="Save"> {t _or} <a href="#" onClick="return false;" class="cancel">{t _cancel}</a>

	</fieldset>
	
{/form}

{include file="layout/dev/foot.tpl"}