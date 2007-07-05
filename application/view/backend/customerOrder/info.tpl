<fieldset class="order_info">
    <legend>{t _order_info}</legend>
    
    <p>
        <label for="order_{$order.ID}_amount">{t _order_id}</label>
        <label>{$order.ID}</label>
    </p>
    
    <p>
        <label for="order_{$order.ID}_user">{t _user}</label>
        <label>
            <a href="#" onclick="Backend.UserGroup.prototype.openUser({$order.User.ID}, event); return false;">
                {$order.User.firstName} {$order.User.lastName}
            </a>
        </label>
    </p>

    <p>
        <label for="order_{$order.ID}_amount">{t _amount}</label>
        <label>
            {$order.Currency.pricePrefix}{$order.capturedAmount|default:0}{$order.Currency.priceSuffix} 
            / 
            {$order.Currency.pricePrefix}{$order.totalAmount|default:0}{$order.Currency.priceSuffix} 
        </label>
    </p>

    <p>
        <label for="order_{$order.ID}_dateCreated">{t _date_created}</label>
        <label>{$order.dateCompleted}</label>
    </p>

    <p>
        <label for="order_{$order.ID})_isPaid">{t _is_paid}</label>    
        <label>{if $order.isPaid}{t _yes}{else}{t _no}{/if}</label>
    </p>
</fieldset>

<fieldset class="container">
    <ul class="menu orderMenu">
        <li>
            <a href="">Print invoice</a>
        </li>
        <li>
            <span style="display: none;" id="order_{$order.ID}_isCanceledIndicator" class="progressIndicator"></span>
            <a id="order_{$order.ID}_isCanceled" href="{link controller="backend.customerOrder" action="setIsCanceled" id=$order.ID}">
                {if $order.isCancelled}{t _accept_order}{else}{t _cancel_order}{/if}
            </a>
        </li>
        <li>
            <a href="">Delete order</a>
        </li>
    </ul>
</fieldset>

<fieldset class="order_status">
    <legend>{t _order_status}</legend>
    {form handle=$form action="controller=backend.customerOrder action=update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.CustomerOrder.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post"}
        {hidden name="ID"} 
        <fieldset class="error">
            <label for="order_{$order.ID}_status">{t _status}</label>
            {selectfield options=$statuses id="order_`$order.ID`_status" name="status" class="status"}
            <img src="image/indicator.gif" id="order_{$order.ID}_status_feedback" style="display: none;"/> 
    	</fieldset>  
    {/form}
    
    <div class="order_acceptanceStatus" >
        {t _this_order_is}
        <span class="order_acceptanceStatusValue" id="order_acceptanceStatusValue_{$order.ID}" style="color: {if $order.isCancelled}red{else}green{/if}">
            {if $order.isCancelled}{t _canceled}{else}{t _accepted}{/if}
        </span>
    </div>
    
    
</fieldset>

<br class="clear" />


{form handle=$formShippingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_shippingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post"}
    <fieldset class="order_shippingAddress">
        <legend>{t _shipping_address}</legend>
        {include file=backend/customerOrder/address.tpl type="shippingAddress" order=$order.ShippingAddress states=$shippingStates}
    </fieldset>
{/form}


{form handle=$formBillingAddress action="controller=backend.customerOrder action=updateAddress" id="orderInfo_`$order.ID`_billingAddress_form" onsubmit="Backend.CustomerOrder.Address.prototype.getInstance(this, false).submitForm(); return false;" method="post"}
    <fieldset class="order_billingAddress">
        <legend>{t _billing_address}</legend>
        {include file=backend/customerOrder/address.tpl type="billingAddress" order=$order.BillingAddress states=$billingStates}
    </fieldset>
{/form}



<script type="text/javascript">
    Backend.CustomerOrder.Editor.prototype.existingUserAddresses = {json array=$existingUserAddresses}
    
    {literal}
    try
    {
        var status = Backend.CustomerOrder.Editor.prototype.getInstance({/literal}{$order.ID}{literal});
        var shippingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_{$order.ID}_shippingAddress_form{literal}'), 'shippingAddress');
        var billingAddress = Backend.CustomerOrder.Address.prototype.getInstance($('{/literal}orderInfo_{$order.ID}_billingAddress_form{literal}'), 'billingAddress');
    }
    catch(e)
    {
        console.info(e);
    }
    {/literal}
</script>