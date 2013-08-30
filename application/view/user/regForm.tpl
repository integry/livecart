{%- macro startinput(field, type) %}
	<div class="row">
{%- endmacro %}

{%- macro endinput() %}
	</div>
{%- endmacro %}

{%- macro textfld(field, title, class = '') %}
    [[ startinput(field, 'textfld') ]]
        <label>[[ t(title) ]]</label>
        [[ text_field(field, 'class': class) ]]
    [[ endinput() ]]
{%- endmacro %}

{%- macro pwdfld(field, title) %}
    [[ startinput(field, 'pwdfld') ]]
        <label>[[ t(title) ]]</label>
        [[ password_field(field) ]]
    [[ endinput() ]]
{%- endmacro %}

{%- macro selectfld(field, title, options) %}
    [[ startinput(field, 'selectfld') ]]
        <label>[[ t(title) ]]</label>
        [[ select(field, options) ]]
    [[ endinput() ]]
{%- endmacro %}

{form action="controller=user action=doRegister" method="POST" handle=$regForm class="form-horizontal"}

	[[ textfld('firstName', '_your_first_name') ]]

	[[ textfld('lastName', '_your_last_name') ]]

	[[ textfld('companyName', '_company_name') ]]

	[[ textfld('email', '_your_email') ]]

	{include file="user/block/passwordFields.tpl" required=true}

	{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

	{block FORM-SUBMIT-REGISTER}

	{include file="block/submit.tpl" caption="_complete_reg" cancelHref=$request.return}

	<input type="hidden" name="return" value="{$request.return|escape}" />

{/form}