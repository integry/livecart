{form handle=$form action="controller=backend.order action=update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.CustomerOrder.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post"}

    <label for="order_{$order.ID}_amount">{t _order_id}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.ID}
	    </div>
	</fieldset>
    
    <label for="order_{$order.ID}_amount">{t _amount}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.capturedAmount|default:0} / {$order.totalAmount|default:0} {$order.Currency.ID}
	    </div>
	</fieldset>


    <label for="order_{$order.ID}_dateCreated">{t _date_created}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.dateCreated}
	    </div>
	</fieldset>
    
    <label for="order_{$order.ID}_dateCompleted">{t _date_completed}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.dateCompleted}
	    </div>
	</fieldset>
    
    <label for="order_{$order.ID})_isPaid">{t _is_paid}</label>    
    <fieldset class="error">
        <div class="formDiv">
            {if $order.isPaid}{t _yes}{else}{t _no}{/if}
	    </div>
	</fieldset>
    
    
    
    <fieldset class="error">
        <label for="order_{$order.ID}_status">{t _status}</label>
        {selectfield options=$statuses id="order_`$order.ID`_status" name="status"}
	</fieldset>  
    
    <fieldset class="error checkbox">
        {checkbox name="isCancelled" id="order_`$order.ID`_isCancelled" readonly="readonly" class="checkbox"}
        <label for="order_{$order.ID}_isCancelled" class="checkbox">{t _is_canceled}</label>
	</fieldset>
    
    <fieldset class="error checkbox">
        {checkbox name="isFinalized" id="order_`$order.ID`_isFinalized" readonly="readonly" class="checkbox"}
        <label for="order_{$order.ID}_isFinalized" class="checkbox">{t _is_finalized}</label>
	</fieldset>
    
    
    <hr />
    
    <label for="order_{$order.ID}_user">{t _user}</label>
    <fieldset class="error">
        <div class="formDiv">
            <a href="#" onclick="Backend.UserGroup.prototype.openUser({$order.User.ID}, event); return false;">
                {$order.User.firstName} {$order.User.lastName}
            </a>
        </div> 
	</fieldset>
    
    <label for="order_{$order.ID}_email">{t _email}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.User.email}
        </div> 
	</fieldset>

    <label for="order_{$order.ID}_shippingAddress1">{t _shipping_address}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.ShippingAddress.countryID} {$order.ShippingAddress.stateName}{if $order.ShippingAddress.city},{/if} {$order.ShippingAddress.city}{if $order.ShippingAddress.address1},{/if} {$order.ShippingAddress.address1} {if $order.ShippingAddress.postalCode}({$order.ShippingAddress.postalCode}){/if}
        </div>
	</fieldset>
    
    {if $order.ShippingAddress.sddress2}
        <label for="order_{$order.ID}_shippingAddress2">{t _shipping_address}</label>
        <fieldset class="error">
            <div class="formDiv">
                {$order.ShippingAddress.countryID} {$order.ShippingAddress.stateName}{if $order.ShippingAddress.city},{/if} {$order.ShippingAddress.city}{if $order.ShippingAddress.address2},{/if} {$order.ShippingAddress.address2} {if $order.ShippingAddress.postalCode}({$order.ShippingAddress.postalCode}){/if}
    	    </div
        </fieldset>
    {/if}    
    
    <label for="order_{$order.ID}_shippingAddress1">{t _billing_address}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.BillingAddress.countryID} {$order.BillingAddress.stateName}{if $order.BillingAddress.city},{/if} {$order.BillingAddress.city}{if $order.BillingAddress.address1},{/if} {$order.BillingAddress.address1} {if $order.BillingAddress.postalCode}({$order.BillingAddress.postalCode}){/if}
	    </div>
    </fieldset>
    
    {if $order.BillingAddress.address2}
        <label for="order_{$order.ID}_billingAddress2">{t _shipping_address}</label>
        <fieldset class="error">
            <div class="formDiv">
                {$order.BillingAddress.countryID} {$order.BillingAddress.stateName}{if $order.BillingAddress.city},{/if} {$order.BillingAddress.city}{if $order.BillingAddress.address2},{/if} {$order.BillingAddress.address2} {if $order.BillingAddress.postalCode}({$order.BillingAddress.postalCode}){/if}
    	    </div>
        </fieldset>
    {/if}
    


    <fieldset class="controls">
    	<input type="submit" name="save" class="submit" value="Save"> {t _or} <a class="cancel" href="#">{t _cancel}</a>
    </fieldset>
    
    
{/form}



<script type="text/javascript">
    {literal}
    try
    {
        Backend.CustomerOrder.Editor.prototype.getInstance({/literal}{$order.ID}{literal});
    }
    catch(e)
    {
        console.info(e);
    }
    {/literal}
</script>