{if !$language || !is_string($language)}
	{% set fieldName = $field.fieldName %}
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
		{if $angular}
			{assign var="ngmodel" value="`$angular`.attributes.`$field.ID`"}
		{/if}

		{control}
		<eav-multiselect values='{json array=$field.values}' ng-model="[[ngmodel]]">
		<div class="multiValueSelect{if $field.type == 1} multiValueNumeric{/if}">

			<div class="eavCheckboxes">
				<div class="input" ng-repeat="value in values | orderBy:sortOrder() | filter:filter">
					<label class="checkbox">
						{checkbox ng_model="value.checked" ng_checked="value.checked == true"}
						{{value.value}}
					</label>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-5">
					{if !$disableNewOptionValues}
						<div ng-repeat="value in newValues">
							{textfield placeholder="{t _other}" class="newOptionValue" ng_model="value.value" ng_change="handleNewValues()" noFormat=true}
						</div>
					{/if}
				</div>

				<div class="col-lg-7 selectMenu">
					<a ng-click="selectAll()" class="eavSelectAll">{t _select_all}</a> | <a ng-click="deselectAll()" class="deselect eavDeselectAll">{t _deselect_all}</a> | <a class="eavSort" ng-click="sort()">A-Z</a> | <input type="text" ng-model="filter" placeholder="{t _eav_filter}" class="text filter" />
				</div>
			</div>
		</div>
		</eav-multiselect>
		{/control}
	{else}
		{if $angular}
			{assign var="ngmodel" value="`$angular`.attributes.`$field.ID`.ID"}
			{assign var="ngmodelnew" value="`$angular`.attributes.`$field.ID`.newValue"}
		{/if}

		{control}
		<eav-select {if !$disableNewOptionValues}new="{t _enter_other}"{/if}>
			<span class="prefix">[[field.valuePrefix_lang]]</span>{selectfield name="`$prefix``$fieldName`" ng_model=$ngmodel options=$field.values class="select" noFormat=true}<span class="suffix">[[field.valueSuffix_lang]]</span>
			{if !$disableNewOptionValues}
				<div class="newOptionValue" style="display: none">
					{textfield ng_model=$ngmodelnew name="`$prefix`other[`$field.ID`]" class="text" noFormat=true}
				</div>
			{/if}
		</eav-select>
		{/control}
	{/if}

{elseif $field.type == 2}
	{control}
		<span class="prefix">[[field.valuePrefix_lang]]</span>{textfield name="`$prefix``$fieldName`" number="float" class="text numeric number" noFormat=true ng_model=$ngmodel noFormat=true}<span class="suffix">[[field.valueSuffix_lang]]</span>
	{/control}

{elseif $field.type == 3}
	{if !$disableAutocomplete}
		{assign var="autocompleteController" value=$autocompleteController|@or:'backend.product'}
		{assign var="autocomplete" value="controller=`$autocompleteController` field=`$fieldName`"}
	{/if}
	{textfield name="`$prefix``$fieldName`" class="text [[textFieldClass]]" ng_model=$ngmodel autocomplete=$autocomplete}

{elseif $field.type == 4}
	{textarea tinymce=true name="`$prefix``$fieldName`" class="tinyMCE" ng_model=$ngmodel}
	<div class="text-error hidden"></div>

{elseif $field.type == 6}
	{calendar name="`$prefix``$fieldName`" ng_model=$ngmodel}
{/if}