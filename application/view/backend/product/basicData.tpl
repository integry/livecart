{defun name="specFieldFactory" field="field" language="language" id="product_form_`$product.ID`"}
	
	{if !$language}
		{assign var="fieldName" value=$field.fieldName}
	{else}
		{assign var="fieldName" value="`$field.fieldName`_`$language`"}
	{/if}
	
	{if $field.type == 1 || $field.type == 5}
		{if $field.isMultiValue}		
			<fieldset class="container multiValueSelect{if $field.type == 1} multiValueNumeric{/if}">
			{foreach from=$field.values key="id" item="value"}
				{if '' != $id}
					<p>
						{checkbox name="specItem_`$id`" class="checkbox" value="on"}<label class="checkbox" for="specItem_{$id}"> {$value}</label>
					</p>
				{/if}
			{/foreach}

			<div class="other">
				<p>
					<label> {t _other}:</label>
					{textfield name="other[`$field.ID`][]"}
				</p>
			</div>

			<p class="selectMenu">
				<a href="#" onclick="Backend.Product.multiValueSelect(this, true); return false;">Select All</a> | <a href="#" onclick="Backend.Product.multiValueSelect(this, false);  return false;" class="deselect">Deselect All</a>
			</p>

			</fieldset>
			<input type="hidden" name="{$fieldName}" value="" />
		{else}
			{selectfield id="`$fieldName`_`$product.ID`" name=$fieldName options=$field.values class="select"}		
		{/if}

	{elseif $field.type == 2}
		{textfield id="`$fieldName`_`$product.ID`" name=$fieldName class="text numeric"}

	{elseif $field.type == 3}
		{textfield id="`$fieldName`_`$product.ID`" name=$fieldName class="text"}

	{elseif $field.type == 4}
		<div class="textarea">
			{textarea id="`$fieldName`_`$product.ID`" name=$fieldName}
		</div>

	{elseif $field.type == 6}
		{calendar id="`$fieldName`_`$product.ID`" name=$fieldName}

	{/if}
{/defun}


{form handle=$productForm action="controller=backend.product action=saveBasic id=`$product.ID`" method="post"}
<fieldset>
    <legend>Classification</legend>
    <p class="required">
    	<label for="sku_addproduct_{$product.ID}">SKU (code):</label>
    	<fieldset class="error">
    		{textfield name="sku" id="sku_addproduct_`$product.ID`" autocomplete="controller=backend.product field=sku"} 
    		<div class="errorText hidden"></div>
    	</fieldset>			
    </p>
</fieldset>


<fieldset>
    <legend>Main Details</legend>
    
	<p class="required">
		<label for="name_addproduct_{$product.ID}">Product name:</label>
        
		<fieldset class="error">
			{textfield name="name" id="name_addproduct_`$product.ID`" class="wide"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="shortdes_addproduct_{$product.ID}">Short description:</label>
        
		<div class="textarea">
			{textarea class="shortDescr" id="shortdes_addproduct_`$product.ID`" name="shortDescription"}
		</div>
	</p>
	<p>
		<label for="longdes_addproduct_{$product.ID}">Long description:</label>
        
		<div class="textarea">
			{textarea class="longDescr" id="longdes_addproduct_`$product.ID`" name="longDescription"}
		</div>
	</p>
	<p>
		<label for="url_addproduct_{$product.ID}">Website address:</label>
        
		<fieldset class="error">
			{textfield name="URL" class="wide" id="url_addproduct_`$product.ID`" autocomplete="controller=backend.product field=URL"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="manufacturer_addproduct_{$product.ID}">Manufacturer:</label>
        
		<fieldset class="error">
			{textfield name="manufacturer" class="wide" autocomplete="controller=backend.manufacturer field=manufacturer" id="manufacturer_addproduct_`$product.ID`"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>
	<p>
		<label for="keywords_addproduct_{$product.ID}">Keywords:</label>
        
		<fieldset class="error">
			{textfield name="keywords" class="wide" id="keywords_addproduct_`$product.ID`" autocomplete="controller=backend.product field=keywords"}
			<div class="errorText hidden"></div>
		</fieldset>			
	</p>

	<p>
		{checkbox name="isBestseller" class="checkbox" value="on" id="isbestseller_addproduct_`$product.ID`"}
		<label for="isbestseller_addproduct_{$product.ID}">Mark as bestseller</label>
	</p>
</fieldset>


{if $specFieldList}
<fieldset class="specification">
	<legend>Product Specification</legend>
	{foreach from=$specFieldList key=groupID item=fieldList}
	
		{if $groupID}
			<fieldset>
				<legend>{$fieldList.0.SpecFieldGroup.name_lang}</legend>
		{/if}
		
		{foreach from=$fieldList item=field}
		<p{if $field.isRequired} class="required"{/if}>		
			<label for="{$field.fieldName}_{$product.ID}">{$field.name_lang}:</label>				
			<fieldset class="error">
				{fun name="specFieldFactory" field=$field}			
				<div class="errorText hidden"></div>
			</fieldset>			
		</p>
		{/foreach}
	
		{if $groupID}
			</fieldset>
		{/if}

	{/foreach}		
</fieldset>
{/if}


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
				{textarea class="shortDescr" name="shortDescription_$lang" id="shortDescription_`$lang`_`$product.ID`"}
			</div>
		</p>
		<p>
			<label>Long description:</label>
			<div class="textarea">
				{textarea class="longDescr" name="longDescription_$lang" id="longDescription_`$lang`_`$product.ID`"}
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
	<input type="submit" name="save" class="submit" value="Save"> {t _or} <a class="cancel" href="#" onclick="Backend.Product.cancelAddProduct({$product.Category.ID}, this.parentNode.parentNode.parentNode); return false;">{t _cancel}</a>
</fieldset>

<script type="text/javascript">
    new SectionExpander("product_form_{$product.ID}");
</script>
{/form}