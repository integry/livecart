<p {if $required}class="required"{/if}>
	{err for="password"}
		{{label {t _password}:}}
		{textfield type="password" class="text"}
	{/err}
</p>

<p {if $required}class="required"{/if}>
	{err for="confpassword"}
		{{label {t _conf_password}:}}
		{textfield type="password" class="text"}
	{/err}
</p>