<form action="user/doRegister" method="POST" handle=$regForm>

	[[ textfld('firstName', '_your_first_name') ]]

	[[ textfld('lastName', '_your_last_name') ]]

	[[ textfld('companyName', '_company_name') ]]

	[[ textfld('email', '_your_email') ]]

	[[ partial("user/block/passwordFields.tpl", ['required': true]) ]]
	[[ partial('user/block/passwordFields.tpl', ['required': true]) ]]

	[[ partial('block/eav/fields.tpl', ['item': $user, 'filter': "isDisplayed"]) ]]

	{block FORM-SUBMIT-REGISTER}

	[[ partial('block/submit.tpl', ['caption': "_complete_reg", 'cancelHref': req('return')]) ]]

	<input type="hidden" name="return" value="{req('return')|escape}" />

</form>