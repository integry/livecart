{includeCSS file="backend/Product.css"}

{includeJS file="library/fckeditor/fckeditor.js"}

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
						{checkbox name="specItem_`$id`" class="checkbox" value="on"}<label for="specItem_{$id}"> {$value}</label>
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
		{textarea id=$fieldName name=$fieldName class="fckEditor"}

	{elseif $field.type == 6}
		{calendar id=$fieldName name=$fieldName}
	{/if}
{/defun}

{form handle=$productForm action="controller=backend.product action=save id=`$product.ID`" method="POST"}
	
	<input type="hidden" name="categoryID" value="{$product.Category.ID}" />
	
	<fieldset>
		<legend>Main product information</legend>

		<p class="required">
			<label for="name">Product name:</label>
			<fieldset class="error">
			{textfield name="name"}
			{error for="name"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p class="required">
			<label for="sku">SKU:</label>
			<fieldset class="error">
			{textfield name="sku"}
			{error for="sku"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p>
			<label for="shortDescription">Short description:</label>
			{textarea class="shortDescr fckEditor" name="shortDescription"}
		</p>

		<p>
			<label for="longDescription">Long description:</label>
			{textarea class="longDescr fckEditor" name="longDescription"}
		</p>

		<p>
			<label for="status">Status:</label>
			{selectfield name="status"}
		</p>

		<p>			
			{checkbox name="isBestseller" class="checkbox"}<label for="isBestseller"> Mark as bestseller</label>
		</p>
		<p>

	</fieldset>

	<fieldset class="specField">
		<legend>Specification Attributes</legend>
		{foreach from=$specFieldList item=field}
		
		<p>		
			<label>{$field.name_lang}:</label>				
			{fun name="specFieldFactory" field=$field}			
		</p>
		
		{/foreach}		
	</fieldset>
	
	<fieldset>
		<legend>Shipping Info</legend>
		<p>
			<label>Height:</label>
			{textfield name="shippingHeight"}
		</p>
		<p>
		</p>
	</fieldset>


	{foreach from=$languageList key=lang item=langName}
	<fieldset class="expandingSection">
		<legend>Translate to: {$langName}</legend>
		<div class="expandingSectionContent">
			<p>
				<label>Product name:</label>
				{textfield name="name_$lang"}
			</p>
			<p>
				<label>Short description:</label>
				{textarea class="shortDescr fckEditor" name="shortDescription_$lang"}
			</p>
			<p>
				<label>Long description:</label>
				{textarea class="longDescr fckEditor" name="longDescription_$lang"}
			</p>
			
			<fieldset>
				<legend>Specification Attributes</legend>
				{foreach from=$multiLingualSpecFields item="field"}
					<p>		
						<label for="{$field.fieldName}_{$lang}">{$field.name_lang}:</label>				
						{fun name="specFieldFactory" field=$field language=$lang}			
					</p>
				{/foreach}
			</fieldset>
			
		</div>
	</fieldset>
	{/foreach}
	
	<input type=submit class="submit" value="Save"> {t _or} <a href="#" onClick="return false;" class="cancel">{t _cancel}</a>
	
{/form}

{include file="layout/dev/foot.tpl"}