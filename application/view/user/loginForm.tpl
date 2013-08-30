<form action="{link controller=user action=doLogin}" method="post" id="loginForm" class="form-horizontal" />
	{input name="email"}
		{label}{t _your_email}:{/label}
		<div class="controls">
			<input type="text" class="text" id="email" name="email" value="{$email|escape}" />
		</div>
	{/input}

	{input name="password"}
		{label}{t _your_pass}:{/label}
		<div class="controls">
			<input type="password" class="text" id="password" name="password" />
			<a href="{link controller=user action="remindPassword" query="return=$return"}" class="forgottenPassword">
				{t _remind_password}
			</a>
		</div>
	{/input}

	[[ partial('block/submit.tpl', ['caption': "_login", 'cancelRoute': $return]) ]]

	<input type="hidden" name="return" value="[[return]]" />

</form>