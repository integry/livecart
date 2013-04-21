<form action="{link controller=user action=doLogin}" method="post" id="loginForm" />
	{input name="email"}
		{label}{t _your_email}:{/label}
		<input type="text" class="text" id="email" name="email" value="{$email|escape}" />
	{/input}

	{input name="password"}
		{label}{t _your_pass}:{/label}
		<input type="password" class="text" id="password" name="password" />
		<a href="{link controller=user action="remindPassword" query="return=$return"}" class="forgottenPassword">
			{t _remind_password}
		</a>
	{/input}

	{include file="block/submit.tpl" caption="_login" cancelRoute=$return}

	<input type="hidden" name="return" value="{$return}" />

</form>