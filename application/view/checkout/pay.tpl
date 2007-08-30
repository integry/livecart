<div class="checkoutPay">

{loadJs form=true}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

	<h1 style="float: left;">{t _pay}</h1>

	{include file="checkout/checkoutProgress.tpl" progress="progressPayment"}
    	   			   	
	<div id="payTotal">
        <div>
			Order total: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
    </div>
		   	
    <h2>Pay securely with a credit card</h2>
        
	<div style="font-size: 90%; width: 600px; margin-left: auto; margin-right: auto; border: 1px solid yellow; padding: 5px; background-color: #FFFCDA; margin-top: 15px; margin-bottom: 15px;">
		Please do not enter real credit card numbers. You can enter any number in the credit card number field. This is not a real transaction. Enter <strong>000</strong> for CVV to test for failed transactions. 
	</div>	       
        
	{form action="controller=checkout action=payCreditCard" handle=$ccForm method="POST"}
    
	    <div style="float: left; width: 500px;">
	    
        {error for="creditCardError"}
	    	<div class="errorMsg ccPayment">
	    		{$msg}
	    	</div>
	    {/error}

	    <p>
			<label for="ccNum">Cardholder name:</label>
            <label>{$order.BillingAddress.fullName}</label>
        </p>

	    <p>
            {err for="ccNum"}
                {{label {t Card number}:}}
	            {textfield class="text" autoComplete="off"}
            {/err}
        </p>
        
        {if $ccTypes}
        <p>
            <label for="ccType">Card type:</label>
            {selectfield name="ccType" options=$ccTypes}
        </p>
        {/if}
    
        <p>
            <label for="ccExpiryMonth">Card expiration:</label>
            <fieldset class="error">
	            {selectfield name="ccExpiryMonth" options=$months}
	            /
	            {selectfield name="ccExpiryYear" options=$years}
				<div class="errorText hidden{error for="ccExpiryYear"} visible{/error}">{error for="ccExpiryYear"}{$msg}{/error}</div>
			</fieldset>
        </p>
    
        <p>
            {err for="ccCVV"}
                {{label {t 3 or 4 digit code after card # on back of card}:}}
	            {textfield maxlength="4" class="text"}
				<a class="cvv" href="{link controller=checkout action=cvv}" onclick="Element.show($('cvvHelp')); return false;">{t What Is It?}</a>
            {/err}
        </p>
        
        <input type="submit" class="submit" value="{tn Complete Order Now}" />
        
        </div>

        <div id="cvvHelp" style="float: left; width: 350px; padding: 5px; margin-left: 20px; display: none;">
    		{include file="checkout/cvvHelp.tpl"}    	
        </div>

    {/form}
    
    
    <div class="clear"></div> 

    {* <h2>Other payment methods</h2> *}

    {include file="checkout/orderOverview.tpl"}
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>