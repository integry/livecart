{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _pay}</h1>
		   	
	<div id="payTotal">
        Order total: <span class="subTotal">{$order.formattedTotal.$currency}</span>
    </div>
		   	
    <h2>Pay with a credit card</h2>
    
    {form action="controller=payment action=payCC" handle=$ccForm}
        <p>
            <label for="ccNum">Card number:</label>
            {textfield name="ccNum"}
        </p>
        
        <p>
            <label for="ccType">Card type:</label>
            {selectfield name="ccType" options=$ccType}
        </p>
    
        <p>
            <label for="ccExpiryMonth">Card expiration:</label>
            {selectfield name="ccExpiryMonth" options=$ccExpiryMonth}
            /
            {selectfield name="ccExpiryYear" options=$ccExpiryYear}
        </p>
    
        <p>
            <label for="ccCVV">3 or 4 digit code after card # on back of card:</label>
            {textfield name="ccCVV" maxlength="4"}
        </p>
        
        <input type="submit" class="submit" value="{tn Complete Order Now}" />
    {/form}
    
    <h2>Other payment methods</h2>    

    <table class="table shipment">            
    {foreach from=$order.shipments key="key" item="shipment"}
        <thead>
            <tr>
                <th class="productName">Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>                            
        </thead>
        <tbody>
            {foreach from=$shipment.items item="item" name="shipment"}
                <tr{zebra loop="shipment"}>                    
                    <td class="productName"><a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a></td>
                    <td>{$item.Product.formattedPrice.$currency}</td>
                    <td>{$item.count}</td>
                    <td>{$item.formattedSubTotal.$currency}</td>
                </tr>
            {/foreach}            
        
            <tr>
                <td colspan="3" class="subTotalCaption">{t _shipping} ({$shipment.selectedRate.serviceName}):</td>
                <td class="subTotal">{$shipment.selectedRate.formattedPrice.$currency}</td>                        
            </tr>
    {/foreach}  
      
            <tr>
                <td colspan="3" class="subTotalCaption">{t _total}:</td>
                <td class="subTotal">{$order.formattedTotal.$currency}</td>                        
            </tr>

        </tbody>        
    </table>    
    
    <div class="clear" />
        
    
</div>

{include file="layout/frontend/footer.tpl"}