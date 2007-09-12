{language}
	<p>
		<label for="product_{$cat}_{$product.ID}_name_{$lang.ID}">{t _product_name}:</label>
		{textfield name="name_`$lang.ID`" class="wide" id="product_`$cat`_`$product.ID`_name_`$lang.ID`"}
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_shortdes_{$lang.ID}">{t _short_description}:</label>
		<div class="textarea">
			{textarea class="shortDescr tinyMCE" name="shortDescription_`$lang.ID`" id="product_`$cat`_`$product.ID`_shortdes_`$lang.ID`"}
		</div>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_longdes_{$lang.ID}">{t _long_description}:</label>
		<div class="textarea">
			{textarea class="longDescr tinyMCE" name="longDescription_`$lang.ID`" id="product_`$cat`_`$product.ID`_longdes_`$lang.ID`"}
		</div>
	</p>
	
	{if $multiLingualSpecFieldss}
	<fieldset>
		<legend>{t _specification_attributes}</legend>
		{foreach from=$multiLingualSpecFieldss item="field"}
			<p>		
				<label for="product_{$cat}_{$product.ID}_{$field.fieldName}_{$lang.ID}">{$field.name_lang}:</label>		
                {include file="backend/product/form/specFieldFactory.tpl" field=$field language=$lang.ID}	
			</p>
		{/foreach}
	</fieldset>
	{/if}
{/language}