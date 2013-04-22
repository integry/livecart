<div class="stepTitle">
	{include file="onePageCheckout/block/modifyStep.tpl"}
	<h2><span class="step">1</span>{t Checkout Options}</h2>
</div>

{if $failedLogin}
	<div class="errorMsg failed">
		{t _login_failed}
	</div>
{/if}

<form method="POST" action="{link controller=onePageCheckout action=doProceedRegistration}" id="checkout-options" class="form-horizontal">
	<h3>{t _new_customer}</h3>
	<p>
		Shopping here for the first time? The next step would be to enter your address and contact information.
	</p>

	{include file="onePageCheckout/block/continueButton.tpl"}
</form>

<form method="POST" action="{link controller=onePageCheckout action=doLogin}" id="checkoutLogin" class="form-horizontal">
	<h3>Returning Customer</h3>
	<p id="login-msg">
		{t _opc_login_msg}
	</p>

	<div class="one-page-checkout-login-field controls">
		<label>{t _your_email}:</label>
		<div class="control-group">
			<input type="text" class="text" id="email" name="email" />
		</div>
	</div>
	<div class="one-page-checkout-login-field controls">
		<label>{t _password}:</label>
		<div class="controls">
			<input type="password" class="text" id="password" name="password" />
			<a href="{link controller=user action="remindPassword" query="return=onePageCheckout"}" class="forgottenPassword">
				{t _remind_password}
			</a>
		</div>
	</div>
	<div class="one-page-checkout-login-field">
		<label class="empty">&nbsp;</label>
		<input type="submit" class="submit" value="{tn _login}" />
	</div>
	<div class="clear"></div>
</form>

{if $preview_options}
	<div class="stepPreview">{$preview_options}</div>
{/if}