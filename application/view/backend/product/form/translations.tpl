{foreach from=$languageList key=lang item=langName}
<fieldset class="expandingSection">
	<legend>{t _translate_to}: {$langName}</legend>
	<div class="expandingSectionContent">
		<p>
			<label fo="product_{$cat}_{$product.ID}_name_{$lang}">{t _product_name}:</label>
			{textfield name="name_$lang" class="wide" id="product_`$cat`_`$product.ID`_name_`$lang`"}
		</p>
		<p>
			<label for="product_{$cat}_{$product.ID}_shortdes_{$lang}">{t _short_description}:</label>
			<div class="textarea">
				{textarea class="shortDescr" name="shortDescription_$lang" id="product_`$cat`_`$product.ID`_shortdes_`$lang`"}
			</div>
		</p>
		<p>
			<label for="product_{$cat}_{$product.ID}_longdes_{$lang}">{t _long_description}:</label>
			<div class="textarea">
				{textarea class="longDescr" name="longDescription_$lang" id="product_`$cat`_`$product.ID`_longdes_`$lang`"}
			</div>
		</p>
		
		{if $multiLingualSpecFieldss}
		<fieldset>
			<legend>{t _specification_attributes}</legend>
			{foreach from=$multiLingualSpecFieldss item="field"}
				<p>		
					<label for="product_{$cat}_{$product.ID}_{$field.fieldName}_{$lang}">{$field}:</label>		
                    {include file="backend/product/form/specFieldFactory.tpl" field=$field language=$lang}	
				</p>
			{/foreach}
		</fieldset>
		{/if}
	</div>
</fieldset>
{/foreach}