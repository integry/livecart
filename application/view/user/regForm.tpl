<form action="user/doRegister" method="POST" handle=$regForm>

	[[ textfld('firstName', '_your_first_name') ]]

	[[ textfld('lastName', '_your_last_name') ]]

	[[ textfld('companyName', '_company_name') ]]

	[[ textfld('email', '_your_email') ]]

	{include file="user/block/passwordFields.tpl" required=true}

	{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

	{block FORM-SUBMIT-REGISTER}

	{include file="block/submit.tpl" caption="_complete_reg" cancelHref=req('return')}

	<input type="hidden" name="return" value="{req('return')|escape}" />

</form>