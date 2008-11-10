{form action="controller=user action=doRegister" method="POST" handle=$regForm}

	<p class="required">
		{err for="firstName"}
			{{label {t _your_first_name}:}}
			{textfield class="text"}
		{/err}
	</p>

	<p class="required">
		{err for="lastName"}
			{{label {t _your_last_name}:}}
			{textfield class="text"}
		{/err}
	</p>

	<p>
		{err for="companyName"}
			{{label {t _company_name}:}}
			{textfield class="text"}
		{/err}
	</p>

	<p class="required">
		{err for="email"}
			{{label {t _your_email}:}}
			{textfield class="text"}
		{/err}
	</p>

	<p class="required">
		{err for="password"}
			{{label {t _password}:}}
			{textfield type="password" class="text"}
		{/err}
	</p>

	<p class="required">
		{err for="confpassword"}
			{{label {t _conf_password}:}}
			{textfield type="password" class="text"}
		{/err}
	</p>

	{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

	<p class="submit">
		<label>&nbsp;</label>
		<input type="submit" class="submit" value="{tn _complete_reg}" />
		{if $request.return}
			<input type="hidden" name="return" value="{$request.return|escape}" />
		{/if}
	</p>

{/form}