<fieldset class="specification">
	<legend>{t _product_specification}</legend>
	{foreach from=$specFieldList key=groupID item=fieldList}
	
		{if $groupID}
			<fieldset>
				<legend>{$fieldList.0.SpecFieldGroup.name_lang}</legend>
		{/if}
		
		{foreach from=$fieldList item=field}
		<p{if $field.isRequired} class="required"{/if}>		
			<label for="product_{$cat}_{$product.ID}_{$field.fieldName}">{$field.name_lang}:</label>				
			<fieldset class="error">
                {include file="backend/product/form/specFieldFactory.tpl" field=$field cat=$cat}		
				<div class="errorText hidden"></div>
			</fieldset>			
		</p>
		{/foreach}
	
		{if $groupID}
			</fieldset>
		{/if}
	{/foreach}		
</fieldset>