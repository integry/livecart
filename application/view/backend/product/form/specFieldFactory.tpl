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
					{checkbox name="specItem_`$id`" class="checkbox" value="on" id="product_`$cat`_`$product.ID`_specItem_`$id`"}
                    <label class="checkbox" for="product_{$cat}_{$product.ID}_specItem_{$id}"> {$value}</label>
				</p>
			{/if}
		{/foreach}

		<div class="other">
			<p>
                <label for="product_{$cat}_{$product.ID}_specItem_other_{$field.ID}"> {t _other}:</label>
				{textfield name="other[`$field.ID`][]" id="product_`$cat`_`$product.ID`_specItem_other_`$field.ID`"}
			</p>
		</div>

		<p class="selectMenu">
			<a href="#" onclick="Backend.Product.multiValueSelect(this, true); return false;">{t _select_all}</a> | <a href="#" onclick="Backend.Product.multiValueSelect(this, false);  return false;" class="deselect">{t _deselect_all}</a>
		</p>

		</fieldset>
		<input class="fieldStatus" name="{$fieldName}" value="" style="display: none;" />
	{else}
		{selectfield id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName options=$field.values class="select"}
		{textfield name="other[`$field.ID`]" id="product_`$cat`_`$product.ID`_specItem_other_`$field.ID`" style="display: none"}
	{/if}

{elseif $field.type == 2}
	{$field.valuePrefix_lang}{textfield id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName class="text numeric"}{$field.valueSuffix_lang}

{elseif $field.type == 3}
	{textfield id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName class="text"}

{elseif $field.type == 4}
	<div class="textarea">
		{textarea id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName}
        <div class="errorText hidden"></div> 
	</div>

{elseif $field.type == 6}
	{calendar id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName}
{/if}