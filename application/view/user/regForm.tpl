{form action="controller=user action=doRegister" method="POST" handle=$regForm class="form-horizontal"}

	{* field name="firstName" label=_your_first_name type=textfield *}

	{input name="firstName"}
		{label}{t _your_first_name}:{/label}
		{textfield}
	{/input}

	{input name="lastName"}
		{label}{t _your_last_name}:{/label}
		{textfield}
	{/input}

	{input name="companyName"}
		{label}{t _company_name}:{/label}
		{textfield}
	{/input}

	{input name="email"}
		{label}{t _your_email}:{/label}
		{textfield}
	{/input}

	{include file="user/block/passwordFields.tpl" required=true}

	{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

	{block FORM-SUBMIT-REGISTER}

	{include file="block/submit.tpl" caption="_complete_reg" cancelHref=$request.return}

	<input type="hidden" name="return" value="{$request.return|escape}" />

{/form}