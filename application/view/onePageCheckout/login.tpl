<div class="stepTitle">
	[[ partial("onePageCheckout/block/modifyStep.tpl") ]]
	<h2><span class="step">1</span>{t Checkout Options}</h2>
</div>

{% if !empty(failedLogin) %}
	<div class="errorMsg failed">
		{t _login_failed}
	</div>
{% endif %}

<form method="POST" action="[[ url("onePageCheckout/doProceedRegistration") ]]" id="checkout-options" class="form-horizontal">
	<h3>{t _new_customer}</h3>
	<p>
		Shopping here for the first time? The next step would be to enter your address and contact information.
	</p>

	[[ partial("onePageCheckout/block/continueButton.tpl") ]]
</form>

<form method="POST" action="[[ url("onePageCheckout/doLogin") ]]" id="checkoutLogin" class="form-horizontal">
	<h3>Returning Customer</h3>
	<p id="login-msg">
		{t _opc_login_msg}
	</p>

	<div class="one-page-checkout-login-field controls">
		<label>{t _your_email}:</label>
		<div class="row">
			<input type="text" class="text" id="email" name="email" />
		</div>
	</div>
	<div class="one-page-checkout-login-field controls">
		<label>{t _password}:</label>
		<div class="controls">
			<input type="password" class="text" id="password" name="password" />
			<a href="[[ url("user/remindPassword", "return=onePageCheckout") ]]" class="forgottenPassword">
				{t _remind_password}
			</a>
		</div>
	</div>
	<div class="one-page-checkout-login-field">
		<label class="empty">&nbsp;</label>
		<input type="submit" class="submit" value="{t _login}" />
	</div>
	<div class="clear"></div>
</form>

{% if !empty(preview_options) %}
	<div class="stepPreview">[[preview_options]]</div>
{% endif %}