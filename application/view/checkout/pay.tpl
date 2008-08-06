<div class="checkoutPay">

{loadJs form=true}
{include file="checkout/layout.tpl"}

<div id="content" class="left right">

	<div class="checkoutHeader">
		<h1>{t _pay}</h1>

		{include file="checkout/checkoutProgress.tpl" progress="progressPayment"}
	</div>

	<div id="payTotal">
		<div>
			{t _order_total}: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
	</div>

	{if 'CC_ENABLE'|config}
		<h2>{t _pay_securely}</h2>

		{include file="checkout/testHandler.tpl"}

		{form action="controller=checkout action=payCreditCard" handle=$ccForm method="POST"}

			<div id="ccForm">

			{error for="creditCardError"}
				<div class="errorMsg ccPayment">
					{$msg}
				</div>
			{/error}

			<p>
				<label for="ccNum">{t _cc_name}:</label>
				<label>{$order.BillingAddress.fullName}</label>
			</p>

			<p>
				{err for="ccNum"}
					{{label {t _cc_number}:}}
					{textfield class="text" autoComplete="off"}
				{/err}
			</p>

			{if $ccTypes}
			<p>
				<label for="ccType">{t _cc_type}:</label>
				{selectfield name="ccType" id="ccType" options=$ccTypes}
			</p>
			{/if}

			<p>
				<label for="ccExpiryMonth">{t _card_exp}:</label>
				<fieldset class="error">
					{selectfield name="ccExpiryMonth" id="ccExpiryMonth" options=$months}
					/
					{selectfield name="ccExpiryYear" id="ccExpiryYear" options=$years}
					<div class="errorText hidden{error for="ccExpiryYear"} visible{/error}">{error for="ccExpiryYear"}{$msg}{/error}</div>
				</fieldset>
			</p>

			<p>
				{err for="ccCVV"}
					{{label {t _cvv_descr}:}}
					{textfield maxlength="4" class="text" id="ccCVV"}
					<a class="cvv" href="{link controller=checkout action=cvv}" onclick="Element.show($('cvvHelp')); return false;">{t _what_is_cvv}</a>
				{/err}
			</p>

			<input type="submit" class="submit" value="{tn _complete_now}" />

			</div>

			<div id="cvvHelp" style="display: none;">
				{include file="checkout/cvvHelp.tpl"}
			</div>

		{/form}

		<div class="clear"></div>
	{else}
		{form action="controller=checkout action=payCreditCard" handle=$ccForm method="POST" id="paymentError"}
			{error for="creditCardError"}
				<div class="clear"></div>
				<div class="errorMsg ccPayment">
					<p>{$msg}</p>
				</div>
				<div class="clear"></div>
			{/error}
		{/form}
	{/if}

	{if $otherMethods}
		{if 'CC_ENABLE'|config}
			<h2>{t _other_methods}</h2>
		{else}
			<h2>{t _select_payment_method}</h2>
		{/if}

		<div id="otherMethods">
			{foreach from=$otherMethods item=method}
				<a href="{link controller=checkout action=redirect id=$method}"><img src="image/payment/{$method}.gif" /></a>
			{/foreach}
		</div>
	{/if}

	{if 'OFFLINE_PAYMENT'|config}
		<h2>{t _pay_offline}</h2>

		{include file="checkout/offlinePaymentInfo.tpl"}

		<form action="{link controller=checkout action=payOffline}" method="POST">
			<input type="submit" value="{tn _offline_complete_payment}" />
		</form>
	{/if}

	<h2>{t _order_overview}</h2>

	{include file="checkout/orderOverview.tpl"}

	{defun name="address"}
		{if $address}
			<p>
				{$address.fullName}
			</p>
			<p>
				{$address.companyName}
			</p>
			<p>
				{$address.address1}
			</p>
			<p>
				{$address.address2}
			</p>
			<p>
				{$address.city}
			</p>
			<p>
				{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
			</p>
			<p>
				{$address.countryName}
			</p>
		{/if}
	{/defun}

	<div id="overviewAddresses">

		{if $order.ShippingAddress}
		<div class="addressContainer">
			<h3>{t _will_ship_to}:</h3>
			{fun name="address" address=$order.ShippingAddress}
			<a href="{link controller=checkout action=selectAddress}">{t _change}</a>
		</div>
		{/if}

		<div class="addressContainer">
			<h3>{t _will_bill_to}:</h3>
			{fun name="address" address=$order.BillingAddress}
			<a href="{link controller=checkout action=selectAddress}">{t _change}</a>
		</div>

		<div class="clear"></div>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>