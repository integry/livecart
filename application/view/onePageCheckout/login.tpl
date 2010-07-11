<h2>{t _returning}</h2>
{if $failedLogin}
	<div class="errorMsg failed">
		{t _login_failed}
	</div>
{/if}
<form method="POST" action="{link controller=onePageCheckout action=doLogin}">
	<div class="one-page-checkout-login-field">
		<label>{t _your_email}:</label>
		<input type="text" class="text" id="email" name="email" />
	</div>
	<div class="one-page-checkout-login-field">
		<label>{t _password}:</label>
		<fieldset class="container">
			<input type="password" class="text" id="password" name="password" />
			<a href="{link controller=user action="remindPassword" query="return=onePageCheckout"}" class="forgottenPassword">
				{t _remind_password}
			</a>
		</fieldset>
	</div>
	<div class="one-page-checkout-login-field">
		<label class="empty">&nbsp;</label>
		<input type="submit" class="submit" value="{tn _login}" />
	</div>
	<div class="clear"></div>
</form>
