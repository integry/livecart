[[ form("user/doLogin", ["method": "POST"]) ]] >

	[[ textfld('email', '_your_email', ['type': 'email']) ]]

	[[ pwdfld('password', '_your_pass') ]]

	<a href="[[ url("user/remindPassword") ]]" class="forgottenPassword">
		{t _remind_password}
	</a>

	[[ partial('block/submit.tpl', ['caption': "_login", 'cancelRoute': ret]) ]]

	<input type="hidden" name="return" value="[[ret]]" />

</form>
