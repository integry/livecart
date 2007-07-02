{includeCss file="backend/Payment.css"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}

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

{form action="controller=backend.payment action=processCreditCard" handle=$ccForm method="POST"}

{error for="creditCardError"}
	<div class="errorMsg ccPayment">
		{$msg}
	</div>
{/error}

<p>
	<label for="ccNum">{t Amount to charge}:</label>   
    <fieldset class="error">
        {textfield name="amount" class="text number"} {$order.Currency.ID}
        <div class="errorText hidden"></div> 
    </fieldset>        
</p>

<p>
	<label for="ccNum">{t Cardholder name}:</label>
    <fieldset class="error">
        {textfield name="name" class="text"}
        <div class="errorText hidden"></div> 
    </fieldset>        
</p>

<p>
	<label for="ccNum">{t Card number}:</label>
    <fieldset class="error">
        {textfield name="ccNum" class="text"}
		<div class="errorText hidden{error for="ccNum"} visible{/error}">{error for="ccNum"}{$msg}{/error}</div>
	</fieldset>
</p>

{if $ccType}
    <p>
        <label for="ccType">{t Card type}:</label>
        {selectfield name="ccType" options=$ccType}
    </p>
{/if}

<p>
    <label for="ccExpiryMonth">{t Card expiration}:</label>
    <fieldset class="error">
        {selectfield name="ccExpiryMonth" class="narrow" options=$months}
        /
        {selectfield name="ccExpiryYear" class="narrow" options=$years}
		<div class="errorText hidden{error for="ccExpiryYear"} visible{/error}">{error for="ccExpiryYear"}{$msg}{/error}</div>
	</fieldset>
</p>

<p>
    <label for="ccCVV">{t CVV code}:</label>
    <fieldset class="error">
        {textfield name="ccCVV" maxlength="4" class="text number"} 
		<div class="errorText hidden{error for="ccCVV"} visible{/error}">{error for="ccCVV"}{$msg}{/error}</div>
	</fieldset>
</p>

<fieldset class="controls">
    <label></label>
    <span class="progressIndicator" style="display: none;"></span>
    <input type="submit" class="submit" value="{tn Process Payment}" />
    {t _or} <a href="#cancel" class="cancel">{t _cancel}</a>
</fieldset>

{/form}

    <div class="clear"></div>

</div>

</body></html>