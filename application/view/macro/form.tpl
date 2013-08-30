{%- macro startinput(field, type) %}
	<div class="form-group field_[[field]] type_[[type]]">
{%- endmacro %}

{%- macro endinput() %}
	</div>
{%- endmacro %}

{%- macro label(title) %}
	<label class="control-label">[[ title ]]</label>
{%- endmacro %}

{#, class=""#}{#, 'class': class#}
{%- macro textfld(field, title) %}
    [[ startinput(field, 'textfld') ]]
        [[ label(t(title)) ]]
        [[ text_field(field, 'class': "form-control") ]]
    [[ endinput() ]]
{%- endmacro %}

{%- macro pwdfld(field, title) %}
    [[ startinput(field, 'pwdfld') ]]
        [[ label(t(title)) ]]
        [[ password_field(field, 'class': "form-control") ]]
    [[ endinput() ]]
{%- endmacro %}

{%- macro selectfld(field, title, options) %}
    [[ startinput(field, 'selectfld') ]]
        [[ label(t(title)) ]]
        [[ select(field, options, 'class': 'form-control') ]]
    [[ endinput() ]]
{%- endmacro %}