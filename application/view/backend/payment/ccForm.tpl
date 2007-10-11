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

<input type="hidden" name="id" value="{$order.ID}" />

{error for="creditCardError"}
	<div class="errorMsg ccPayment">
		{$msg}
	</div>
{/error}

<p>
	<label for="ccNum">{t _amount_charge}:</label>   
    <fieldset class="error">
        {textfield name="amount" class="text number"} {$order.Currency.ID}
        <div class="errorText hidden"></div> 
    </fieldset>        
</p>

<p>
	<label for="ccNum">{t _cc_name}:</label>
    <fieldset class="error">
        {textfield name="name" class="text"}
        <div class="errorText hidden"></div> 
    </fieldset>        
</p>

<p>
	<label for="ccNum">{t _cc_num}:</label>
    <fieldset class="error">
        {textfield name="ccNum" class="text"}
		<div class="errorText hidden{error for="ccNum"} visible{/error}">{error for="ccNum"}{$msg}{/error}</div>
	</fieldset>
</p>

{if $ccTypes}
    <p>
        <label for="ccType">{t _cc_type}:</label>
        {selectfield name="ccType" options=$ccTypes}
    </p>
{/if}

<p>
    <label for="ccExpiryMonth">{t _cc_exp}:</label>
    <fieldset class="error">
        {selectfield name="ccExpiryMonth" class="narrow" options=$months}
        /
        {selectfield name="ccExpiryYear" class="narrow" options=$years}
		<div class="errorText hidden{error for="ccExpiryYear"} visible{/error}">{error for="ccExpiryYear"}{$msg}{/error}</div>
	</fieldset>
</p>

<p>
    <label for="ccCVV">{t _cc_cvv}:</label>
    <fieldset class="error">
        {textfield name="ccCVV" maxlength="4" class="text number"} 
		<div class="errorText hidden{error for="ccCVV"} visible{/error}">{error for="ccCVV"}{$msg}{/error}</div>
	</fieldset>
</p>

<p>
    <label for="comment">{t _comment}:</label>
    <fieldset class="error">
        {textarea name="comment"} 
		<div class="errorText hidden{error for="comment"} visible{/error}">{error for="comment"}{$msg}{/error}</div>
	</fieldset>
</p>

<fieldset class="controls">
    <span class="progressIndicator" style="display: none;"></span>
    <input type="submit" class="submit" value="{tn _process}" />
    {t _or} <a href="#cancel" onclick="window.close(); return false;" class="cancel">{t _cancel}</a>
</fieldset>

{/form}

    <div class="clear"></div>

</div>

</body></html>