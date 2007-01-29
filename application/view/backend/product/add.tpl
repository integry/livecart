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

			<div>
				<a href="#" onclick="Backend.Product.multiValueSelect(this, true);">Select All</a> | <a href="#" onclick="Backend.Product.multiValueSelect(this, false);">Deselect All</a>
			</div>

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
		<p>

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
			{textfield name="sku"}
			{error for="sku"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
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
			<label for="website">Website address:</label>
			<fieldset class="error">
				{textfield name="website" class="wide"}
				{error for="website"}<div class="errorText">{$msg}</div>{/error}
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
		{foreach from=$specFieldList item=field}
		
		<p>		
			<label>{$field.name_lang}:</label>				
			{fun name="specFieldFactory" field=$field}			
		</p>
		
		{/foreach}		
	</fieldset>
	
	<fieldset>
		<legend>Inventory</legend>
		<p class="required">
			<label>Items in stock:</label>
			{textfield name="stockCount" class="number" value="0"}
		</p>
	</fieldset>

	<fieldset>
		<legend>Pricing</legend>
		<p class="required">
			<label>Price:</label>
			{textfield name="price_$baseCurrency" class="money"} {$baseCurrency}
		</p>
		{foreach from=$otherCurrencies item="currency"}
		<p>
			<label>Price:</label>
			{textfield name="price_$currency" class="money"} {$currency}
		</p>		
		{/foreach}
	</fieldset>

	<fieldset>
		<legend>Shipping</legend>

		<p style="color:red;">
			<label>Shipping Weight:</label>
			{textfield name="shippingWeight" class="number"}
		</p>

		<p>
			<label>Minimum Order Quantity:</label>
			{textfield name="minimumQuantity" class="number" value="0"}
		</p>

		<p>
			<label>Shipping Surcharge:</label>
			{textfield name="minimumQuantity" class="number"} {$baseCurrency}
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
			
			{if $multiLingualSpecFields.length > 0}
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

	<input type=submit class="submit" value="Save"> {t _or} <a href="#" onClick="return false;" class="cancel">{t _cancel}</a>
	
{/form}

{include file="layout/dev/foot.tpl"}