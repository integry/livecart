<div class="checkoutPay">

{loadJs form=true}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <div class="checkoutHeader">
    	<h1>{t _pay}</h1>
    
    	{include file="checkout/checkoutProgress.tpl" progress="progressPayment"}
    </div>
    	   			   	
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

    <h2>Order Overview</h2>
    
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
		<div style="width: 50%; float: left;">
            <h3>{t Order will be shipped to}:</h3>
            {fun name="address" address=$order.ShippingAddress}
            <a href="{link controller=checkout action=selectAddress}">Change</a>
        </div>    
        {/if}
        
        <div style="width: 50%; float: left;">
            <h3>{t Order will be billed to}:</h3>
            {fun name="address" address=$order.BillingAddress}
            <a href="{link controller=checkout action=selectAddress}">Change</a>
        </div>    
    
        <div class="clear"></div>
    
    </div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>