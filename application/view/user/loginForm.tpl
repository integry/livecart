<form action="{link controller=user action=doLogin}" method="POST" />
	<p>
	   <label for="email">{t _your_email}:</label>
	   <input type="text" class="text" id="email" name="email" value="{$email|escape}" />
	</p>
	<p>
		<label for="password">{t _your_pass}:</label>
		<fieldset class="container">
			<input type="password" class="text" id="password" name="password" />
			<a href="{link controller=user action="remindPassword" query="return=$return"}" class="forgottenPassword">
				{t _remind_password}
			</a>
		</fieldset>
	</p>

   	<p class="submit">
		<label></label>
		<input type="submit" class="submit" value="{tn _login}" />
	</p>

	<input type="hidden" name="return" value="{$return}" />

</form>