<h1>Set Up Administrator User Account</h1>

<div>

	{form action="install/setAdmin" method="POST" handle=$form class="form-horizontal"}

		[[ textfld('firstName', '_first_name') ]]

		[[ textfld('lastName', '_last_name') ]]

		[[ textfld('email', '_email') ]]

		[[ pwdfld('password', '_password') ]]

		[[ pwdfld('confirmPassword', '_confirm_password') ]]

		<div class="clear"></div>
		<input type="submit" value="Continue installation" />
	{/form}
</div>

{literal}
<script type="text/javascript">
	$('firstName').focus();
</script>
{/literal}
