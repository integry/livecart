{if !$language || !is_string($language)}
	{assign var="fieldName" value=$field.fieldName}
{else}
	{assign var="fieldName" value="`$field.fieldName`_`$language`"}
{/if}

{if $angular}
	{assign var="ngmodel" value="`$angular`.attributes.`$field.ID`.value"}
	{if $language}
		{assign var="ngmodel" value="`$ngmodel`_`$language`"}
	{/if}
{/if}

{if $field.type == 1 || $field.type == 5}
	{if $field.isMultiValue}
		<div class="controls multiValueSelect{if $field.type == 1} multiValueNumeric{/if}">

			<div class="eavCheckboxes">
				{foreach from=$field.values key="id" item="value"}
					{if '' != $id}
						<div class="input">
							{checkbox name="`$prefix`specItem_`$id`" class="checkbox" value="on"}
							<label class="checkbox" for="product_{$cat}_{$item.ID}_specItem_{$id}"> {$value}</label>
						</div>
					{/if}
				{/foreach}
			</div>

		{if !$disableNewOptionValues}
			<div class="other">
				<p>
					<label for="product_{$cat}_{$item.ID}_specItem_other_{$field.ID}"> {t _other}:</label>
					{textfield name="`$prefix`other[`$field.ID`][]"}
				</p>
			</div>
		{/if}

		<p class="selectMenu">
			<a href="#" class="eavSelectAll">{t _select_all}</a> | <a href="#" class="deselect eavDeselectAll">{t _deselect_all}</a> | <a class="eavSort" href="#">A-Z</a> | {t _eav_filter}: <input type="text" class="text filter" />
		</p>

		</div>
		<input class="fieldStatus" name="{$fieldName}" value="" style="display: none;" />
	{else}
		{if !$disableNewOptionValues}
			{php}$field = $smarty->getTemplateVars('field'); $field['values']['other'] = $smarty->getApplication()->translate('_enter_other'); $smarty->assign('field', $field);{/php}
		{/if}
		<span class="prefix">{$field.valuePrefix_lang}</span>{selectfield name="`$prefix``$fieldName`" options=$field.values class="select"}<span class="suffix">{$field.valueSuffix_lang}</span>
		{if !$disableNewOptionValues}
			{textfield name="`$prefix`other[`$field.ID`]" style="display: none" class="text"}
		{/if}
	{/if}

{elseif $field.type == 2}
	<div class="controls">
		<span class="prefix">{$field.valuePrefix_lang}</span>{textfield name="`$prefix``$fieldName`" class="text numeric number" noFormat=true ng_model=$ngmodel}<span class="suffix">{$field.valueSuffix_lang}</span>
	</div>

{elseif $field.type == 3}
	{if !$disableAutocomplete}
		{assign var="autocompleteController" value=$autocompleteController|@or:'backend.product'}
		{assign var="autocomplete" value="controller=`$autocompleteController` field=`$fieldName`"}
	{/if}
	{textfield name="`$prefix``$fieldName`" class="text {$textFieldClass}" ng_model=$ngmodel autocomplete=$autocomplete}

{elseif $field.type == 4}
	{textarea tinymce=true name="`$prefix``$fieldName`" class="tinyMCE" ng_model=$ngmodel}
	<div class="text-error hidden"></div>

{elseif $field.type == 6}
	{calendar name="`$prefix``$fieldName`" ng_model=$ngmodel}
{/if}