<h1>Set Up Administrator User Account</h1>

<div>

	{form action="controller=install action=setAdmin" method="POST" handle=$form}

		{input name="firstName"}
			{label}{t _first_name}:{/label}
			{textfield}
		{/input}

		{input name="lastName"}
			{label}{t _last_name}:{/label}
			{textfield}
		{/input}

		{input name="email"}
			{label}{t _email}:{/label}
			{textfield}
		{/input}

		{input name="password"}
			{label}{t _password}:{/label}
			{textfield class="password"}
		{/input}

		{input name="confirmPassword"}
			{label}{t _confirm_password}:{/label}
			{textfield class="password"}
		{/input}

		<div class="clear"></div>
		<input type="submit" value="Continue installation" />
	{/form}
</div>

{literal}
<script type="text/javascript">
	$('firstName').focus();
</script>
{/literal}