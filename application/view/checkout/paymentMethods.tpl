{if 'CC_ENABLE'|config}
	<h2>{t _pay_securely}</h2>

	{include file="checkout/testHandler.tpl"}

	{if $id}{assign var=ccId value=" id=`$id`"}{/if}
	{form action="controller=checkout action=payCreditCard`$ccId`" handle=$ccForm method="POST"}

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

		<p>
			<input type="submit" class="submit" value="{tn _complete_now}" />
		</p>

		</div>


	{/form}

	<div id="cvvHelp" style="display: none;">
		{include file="checkout/cvvHelp.tpl"}
	</div>

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
			{if $id}{assign var="query" value="order=`$id`"}{/if}
			<a href="{link controller=checkout action=redirect id=$method query=$query}"><img src="image/payment/{$method}.gif" /></a>
		{/foreach}
	</div>
{/if}
