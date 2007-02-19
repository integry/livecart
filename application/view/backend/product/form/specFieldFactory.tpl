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
					{checkbox name="specItem_`$id`" class="checkbox" value="on" id="product_`$cat`_`$product.ID`_specItem_`$id`"}<label class="checkbox" for="product_{$cat}_{$product.ID}_specItem_{$id}"> {$value}</label>
				</p>
			{/if}
		{/foreach}

		<div class="other">
			<p>
				{math equation="rand(x, y)" x=1000000 y=9999999 assign="random"}
                <label for="product_{$cat}_{$product.ID}_specItem_other_{$random}"> {t _other}:</label>
				{textfield name="other[`$field.ID`][]" id="product_`$cat`_`$product.ID`_specItem_other_`$random`"}
			</p>
		</div>

		<p class="selectMenu">
			<a href="#" onclick="Backend.Product.multiValueSelect(this, true); return false;">Select All</a> | <a href="#" onclick="Backend.Product.multiValueSelect(this, false);  return false;" class="deselect">Deselect All</a>
		</p>

		</fieldset>
		<input type="hidden" name="{$fieldName}" value="" />
	{else}
		{selectfield id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName options=$field.values class="select"}		
	{/if}

{elseif $field.type == 2}
	{textfield id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName class="text numeric"}

{elseif $field.type == 3}
	{textfield id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName class="text"}

{elseif $field.type == 4}
	<div class="textarea">
		{textarea id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName}
	</div>

{elseif $field.type == 6}
	{calendar id="product_`$cat`_`$product.ID`_`$fieldName`" name=$fieldName}

{/if}