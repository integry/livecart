{includeCss file="backend/Payment.css"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}

{pageTitle}{t _add_credit_card_payment}{/pageTitle}
{include file="layout/backend/meta.tpl"}

{literal}
	<style>
		body
		{
			background-image: none;
		}
	</style>
{/literal}

<div id="ccForm">

{form action="controller=backend.payment action=processCreditCard id=`$order.ID`" onsubmit="new window.opener.Backend.Payment.AddCreditCard(this, window); return false;" handle=$ccForm method="POST"}

<input type="hidden" name="id" value="[[order.ID]]" />

{error for="creditCardError"}
	<div class="errorMsg ccPayment">
		[[msg]]
	</div>
{/error}

{input name="amount"}
	{label}{t _amount}:{/label}
	{textfield class="text number"} [[order.Currency.ID]]
{/input}

{input name="name"}
	{label}{t _cc_name}:{/label}
	{textfield}
{/input}

{input name="ccNum"}
	{label}{t _cc_num}:{/label}
	{textfield}
{/input}

{if $ccTypes}
	{input name="ccType"}
		{label}{t _cc_type}:{/label}
		{selectfield options=$ccTypes}
	{/input}
{/if}

{input name="ccExpiryYear"}
	{label}{t _cc_exp}:{/label}
	{selectfield name="ccExpiryMonth" class="narrow" options=$months}
	/
	{selectfield name="ccExpiryYear" class="narrow" options=$years}
{/input}

{input name="ccCVV"}
	{label}{t _cc_cvv}:{/label}
	{textfield maxlength="4" class="text number"}
{/input}

{input name="comment"}
	{label}{t _comment}:{/label}
	{textarea}
{/input}

<fieldset class="controls">
	<span class="progressIndicator" style="display: none;"></span>
	<input type="submit" class="submit" value="{tn _process}" />
	{t _or} <a href="#cancel" onclick="window.close(); return false;" class="cancel">{t _cancel}</a>
</fieldset>

{/form}

	<div class="clear"></div>

</div>

</body></html>