<div class="checkoutPay">

{loadJs form=true}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _pay}</h1>
		   	
	<div style="font-size: 90%; width: 600px; margin-left: auto; margin-right: auto; border: 1px solid yellow; padding: 5px; background-color: #FFFCDA; margin-top: 25px; margin-bottom: 25px;">
		Please do not enter real credit card numbers. You can enter any number in the credit card number field. This is not a real transaction. Enter <strong>000</strong> for CVV to test for failed transactions. 
		<div style="margin-top: 5px;">Yes, this page will be under SSL in the product release.</div>
	</div>	
		   	
	<div id="payTotal">
        <div>
			Order total: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
    </div>
		   	
    <h2>Pay with a credit card</h2>
        
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
			<label for="ccNum">Card number:</label>
            <fieldset class="error">
	            {textfield name="ccNum" class="text"}
				<div class="errorText hidden{error for="ccNum"} visible{/error}">{error for="ccNum"}{$msg}{/error}</div>
			</fieldset>
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
            <label for="ccCVV">3 or 4 digit code after card # on back of card:</label>
            <fieldset class="error">
	            {textfield name="ccCVV" maxlength="4" class="text"} 
				<a class="cvv" href="{link controller=checkout action=cvv}" onclick="Element.show($('cvvHelp')); return false;">{t What Is It?}</a>
				<div class="errorText hidden{error for="ccCVV"} visible{/error}">{error for="ccCVV"}{$msg}{/error}</div>
			</fieldset>
        </p>
        
        <input type="submit" class="submit" value="{tn Complete Order Now}" />
        
        </div>

        <div id="cvvHelp" style="float: left; width: 350px; padding: 5px; margin-left: 20px; display: none;">
    		{include file="checkout/cvvHelp.tpl"}    	
        </div>

    {/form}
    
    
    <div class="clear"></div> 

    {* <h2>Other payment methods</h2> *}

    <table class="table shipment" id="payItems">            
        <thead>
            <tr>
                <th class="productName">Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>                            
        </thead>
        <tbody>

        {foreach from=$order.shipments key="key" item="shipment"}
            {include file="order/orderTableDetails.tpl"}
        {/foreach}  
      
        {foreach from=$order.taxes.$currency item="tax"}
            <tr>                    
                <td colspan="3" class="tax">{$tax.name_lang}:</td>
                <td>{$tax.formattedAmount}</td>
            </tr>
        {/foreach}        
          
        <tr>
            <td colspan="3" class="subTotalCaption">{t _total}:</td>
            <td class="subTotal">{$order.formattedTotal.$currency}</td>                        
        </tr>

        </tbody>        
    </table>    
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>