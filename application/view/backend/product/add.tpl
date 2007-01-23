{includeCSS file="backend/Product.css"}

{include file="layout/dev/head.tpl"}

{form handle=$productForm action="controller=backend.product action=save id=`$product.ID`" method="POST"}
	
	<input type="hidden" name="categoryID" value="{$product.Category.ID}" />
	
	<fieldset>
		<legend>Main product information</legend>

		<p class="required">
			<label>Product name:</label>
			<fieldset class="error">
			{textfield name="name"}
			{error for="name"}<div class="errorText">{$msg}</div>{/error}
			</fieldset>			
		</p>

		<p class="required">
			<label>SKU:</label>
			{textfield name="SKU"}
		</p>

		<p>
			<label>Short description:</label>
			{textarea class="shortDescr" name="shortDescription"}
		</p>

		<p>
			<label>Long description:</label>
			{textarea class="longDescr" name="longDescription"}
		</p>

		<p>
			<label>Status:</label>
			{selectfield name="status"}
		</p>

		<p>
			Is bestseller
			{checkbox name="isBestseller"}
		</p>
		<p>

	</fieldset>

	<fieldset class="specField">
		<legend>Specification Attributes</legend>
		{foreach from=$specFieldList item=field}
		
		<p>
		
			<label>{$field.name_lang}:</label>
				
			{if $field.type == 1}
				{selectfield id=$field.fieldName name=$field.fieldName options=$field.values class="select"}

			{elseif $field.type == 2}
				{textfield id=$field.fieldName name=$field.fieldName class="text numeric"}

			{elseif $field.type == 3}
				{textfield id=$field.fieldName name=$field.fieldName class="text"}

			{elseif $field.type == 4}
				{textarea id=$field.fieldName name=$field.fieldName }

			{elseif $field.type == 5}
				{selectfield id=$field.fieldName name=$field.fieldName options=$field.values class="select"}

			{elseif $field.type == 6}
				{calendar id=$field.fieldName name=$field.fieldName}
			{/if}
			
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
				{textarea name="shortDescription_$lang"}
			</p>
			<p>
				<label>Long description:</label>
				{textarea name="longDescription_$lang"}
			</p>
		</div>
	</fieldset>
	{/foreach}
	
	<input type=submit value="Save">
	
{/form}

{include file="layout/dev/foot.tpl"}