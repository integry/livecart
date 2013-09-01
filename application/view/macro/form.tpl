{%- macro form(action, params = '') %}
	action="[[ url(action) ]]" ng-init="isSubmitted=0"
	{% if !empty(params['method']) %}
		method="[[ params['method'] ]]"
	{% endif %}
{%- endmacro %}

{%- macro startinput(field, type) %}
	<div class="form-group field_[[field]] type_[[type]]">
{%- endmacro %}

{%- macro endinput(field) %}
	{% set validator = global('validator') %}
	{% if !empty(validator) and validator.getValidators(field) %}
		<div ng-show="isSubmitted && form.form.$error.[[field]]" class="text-danger">Error</div>
	{% endif %}
	</div>
{%- endmacro %}

{%- macro open_label() %}
	<label class="control-label">
{%- endmacro %}

{%- macro label(title) %}
	[[ open_label() ]][[ title ]]</label>
{%- endmacro %}

{#, class=""#}{#, 'class': class#}
{%- macro textfld(field, title, params) %}
    [[ startinput(field, 'textfld', params) ]]
        [[ label(t(title)) ]]
        [[ text_field(field, 'class': "form-control", 'ng-model': field, 'ng-required': param('ng-required')) ]]
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro pwdfld(field, title, params) %}
    [[ startinput(field, 'pwdfld') ]]
        [[ label(t(title)) ]]
        [[ password_field(field, 'class': "form-control", 'ng-model': field, 'ng-required': param('ng-required')) ]]
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro selectfld(field, title, options, params) %}
    [[ startinput(field, 'selectfld') ]]
        [[ label(t(title)) ]]
        [[ select(field, options, 'class': 'form-control', 'ng-model': field, 'ng-required': param('ng-required')) ]]
    [[ endinput(field) ]]
{%- endmacro %}

{%- macro checkbox(field, title, params) %}
    [[ startinput(field, 'pwdfld') ]]
        [[ open_label() ]]
        	[[ check_field(field, 'ng-model': field, 'ng-required': param('ng-required')) ]]
        	[[ t(title) ]]
        </label>
    [[ endinput(field) ]]
{%- endmacro %}