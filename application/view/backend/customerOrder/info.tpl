<fieldset class="order_info">
    <legend>{t _order_info}</legend>
    <label for="order_{$order.ID}_amount">{t _order_id}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.ID}
	    </div>
	</fieldset>
        
    <label for="order_{$order.ID}_user">{t _user}</label>
    <fieldset class="error">
        <div class="formDiv">
            <a href="#" onclick="Backend.UserGroup.prototype.openUser({$order.User.ID}, event); return false;">
                {$order.User.firstName} {$order.User.lastName}
            </a>
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

    <label for="order_{$order.ID})_isPaid">{t _is_paid}</label>    
    <fieldset class="error">
        <div class="formDiv">
            {if $order.isPaid}{t _yes}{else}{t _no}{/if}
	    </div>
	</fieldset>
</fieldset>

<fieldset class="order_status">
    <legend>{t _order_status}</legend>
    {form handle=$form action="controller=backend.order action=update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.CustomerOrder.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post"}
        <fieldset class="error">
            <label for="order_{$order.ID}_status">{t _status}</label>
            {selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status"} 
    	</fieldset>  
        
        <fieldset class="error">
            <label></label>
            <a class="isCanceled" href="{link controller="backend.customerOrder" action="setIsCanceled" id=$order.id}">{if $order.isCancelled}{t _cancelled}{else}{t _applied}{/if}</a>
    	</fieldset>
    {/form}
</fieldset>


<br class="clear" />


{form handle=$formShippingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_shippingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post"}
    <fieldset class="order_shippingAddress">
        <legend>{t _shipping_address}</legend>
        {include file=backend/customerOrder/address.tpl type="shippingAddress" order=$order.ShippingAddress}
    </fieldset>
    
{/form}


{form handle=$formBillingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_billingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post"}
    <fieldset class="order_billingAddress">
        <legend>{t _billing_address}</legend>
        {include file=backend/customerOrder/address.tpl type="billingAddress" order=$order.BillingAddress}
    </fieldset>
{/form}



<script type="text/javascript">
    {literal}
    try
    {
        var status = Backend.CustomerOrder.Editor.prototype.getInstance({/literal}{$order.ID}{literal});
        var shippingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_{$order.ID}_shippingAddress_form{literal}'));
        var billingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_{$order.ID}_billingAddress_form{literal}'));
    }
    catch(e)
    {
        console.info(e);
    }
    {/literal}
</script>