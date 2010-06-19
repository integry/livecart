<h2>{t _returning}</h2>
<div id="checkout-failed-login" style="display: none;"></div>
<form method="POST" action="{link controller=onePageCheckout action=doLogin}">
	<div class="one-page-checkout-login-field">
		<label>{t _your_email}:<label>
		<input type="text" class="text" id="email" name="email" />
	</div>
	<div class="one-page-checkout-login-field">
		<label>{t _password}:<label>
		<fieldset class="container">
			<input type="password" class="text" id="password" name="password" />
			<a href="{link controller=user action="remindPassword" query="return=$return"}" class="forgottenPassword">
				{t _remind_password}
			</a>
		</fieldset>
	</div>
	<div class="one-page-checkout-login-field">
		<input type="submit" class="submit" value="{tn _login}" />
	</div>
	<div class="clear"></div>
</form>
