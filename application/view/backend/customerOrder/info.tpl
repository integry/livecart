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
    
    <label for="order_{$order.ID}_dateCompleted">{t _date_completed}</label>
    <fieldset class="error">
        <div class="formDiv">
            {$order.dateCompleted}
            <div class="errorText" style="display: none" ></span>
	    </div>
	</fieldset>

    <label for="order_{$order.ID})_isPaid">{t _is_paid}</label>    
    <fieldset class="error">
        <div class="formDiv">
            {if $order.isPaid}{t _yes}{else}{t _no}{/if}
	    </div>
	</fieldset>
</fieldset>

{form handle=$form action="controller=backend.order action=update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.CustomerOrder.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post"}
    <fieldset class="order_status">
        <legend>{t _order_status}</legend>
        <fieldset class="error">
            <label for="order_{$order.ID}_status" class="checkbox">{t _status}</label>
            {selectfield options=$statuses id="order_`$order.ID`_status" name="status"}
    	</fieldset>  
        
        <fieldset class="error">
            <label></label>
            <a href="{link controller="backend.order" action="setIsCanceled" id=$order.id}">{if $order.isCancelled}{t _canceled}{else}{t _applyed}{/if}</a>
    	</fieldset>
    </fieldset>
{/form}


<br class="clear" />


{form handle=$formShippingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_shippingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post"}
    <fieldset class="order_shippingAddress">
        <legend>{t _shipping_address}</legend>
        {include file=backend/customerOrder/address.tpl type="shippingAddress"}
    </fieldset>
    
{/form}


{form handle=$formBillingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_billingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post"}
    <fieldset class="order_billingAddress">
        <legend>{t _billing_address}</legend>
        {include file=backend/customerOrder/address.tpl type="billingAddress"}
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