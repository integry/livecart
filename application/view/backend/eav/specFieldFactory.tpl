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
					{checkbox name="specItem_`$id`" class="checkbox" value="on" id="product_`$cat`_`$item.ID`_specItem_`$id`"}
					<label class="checkbox" for="product_{$cat}_{$item.ID}_specItem_{$id}"> {$value}</label>
				</p>
			{/if}
		{/foreach}

		{if !$disableNewOptionValues}
			<div class="other">
				<p>
					<label for="product_{$cat}_{$item.ID}_specItem_other_{$field.ID}"> {t _other}:</label>
					{textfield name="other[`$field.ID`][]" id="product_`$cat`_`$item.ID`_specItem_other_`$field.ID`"}
				</p>
			</div>
		{/if}

		<p class="selectMenu">
			<a href="#" class="eavSelectAll">{t _select_all}</a> | <a href="#" class="deselect eavDeselectAll">{t _deselect_all}</a>
		</p>

		</fieldset>
		<input class="fieldStatus" name="{$fieldName}" value="" style="display: none;" />
	{else}
		{if !$disableNewOptionValues}
			{php}$field = $this->get_template_vars('field'); $field['values']['other'] = $this->getApplication()->translate('_enter_other'); $this->assign('field', $field);{/php}
		{/if}
		{selectfield id="product_`$cat`_`$item.ID`_`$fieldName`" name=$fieldName options=$field.values class="select"}
		{if !$disableNewOptionValues}
			{textfield name="other[`$field.ID`]" id="product_`$cat`_`$item.ID`_specItem_other_`$field.ID`" style="display: none" class="text wide"}
		{/if}
	{/if}

{elseif $field.type == 2}
	{$field.valuePrefix_lang}{textfield id="product_`$cat`_`$item.ID`_`$fieldName`" name=$fieldName class="text numeric"}{$field.valueSuffix_lang}

{elseif $field.type == 3}
	{if !$disableAutocomplete}
		{assign var="autocomplete" value="controller=backend.product field=`$fieldName`"}
	{/if}
	{textfield id="product_`$cat`_`$item.ID`_`$fieldName`" name=$fieldName class="text wide" autocomplete=$autocomplete}

{elseif $field.type == 4}
	<div class="textarea" style="margin-left: 0;">
		{textarea id="product_`$cat`_`$item.ID`_`$fieldName`" name=$fieldName class="tinyMCE"}
		<div class="errorText hidden"></div>
	</div>

{elseif $field.type == 6}
	{calendar id="product_`$cat`_`$item.ID`_`$fieldName`" name=$fieldName}
{/if}