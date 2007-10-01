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
			{t _order_total}: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
    </div>
		   	
    {if 'CC_ENABLE'|config}
        <h2>{t _pay_securely}</h2>
            
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
    
            <div id="cvvHelp" style="float: left; width: 350px; padding: 5px; margin-left: 20px; display: none;">
        		{include file="checkout/cvvHelp.tpl"}    	
            </div>
    
        {/form}    
        
        <div class="clear"></div>
    {else}
    	{form action="controller=checkout action=payCreditCard" handle=$ccForm method="POST"}
            {error for="creditCardError"}
    	    	<div class="errorMsg ccPayment">
    	    		{$msg}
    	    	</div>
    	    	<div class="clear"></div>
    	    {/error}    
    	{/form}
    {/if}

    {if $otherMethods}
        {if 'CC_ENABLE'|config}
            <h2>{t Other payment methods}</h2>
        {else}
            <h2>{t Select a payment method}</h2>
        {/if}
        
        <div id="otherMethods">
            {foreach from=$otherMethods item=method}
                <a href="{link controller=checkout action=redirect id=$method}"><img src="image/payment/{$method}.gif" /></a>
            {/foreach}
        </div>
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
		<div style="width: 50%; float: left;">
            <h3>{t _will_ship_to}:</h3>
            {fun name="address" address=$order.ShippingAddress}
            <a href="{link controller=checkout action=selectAddress}">{t _change}</a>
        </div>    
        {/if}
        
        <div style="width: 50%; float: left;">
            <h3>{t _will_bill_to}:</h3>
            {fun name="address" address=$order.BillingAddress}
            <a href="{link controller=checkout action=selectAddress}">{t _change}</a>
        </div>    
    
        <div class="clear"></div>
    
    </div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>