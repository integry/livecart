{form handle=$form action="controller=backend.order action=update" id="orderInfo_`$order.ID`_form" onsubmit="Backend.User.Editor.prototype.getInstance(`$order.ID`, false).submitForm(); return false;" method="post" role="order.create(backend.orderGroup/index),order.update(backend.order/info)"}


   	<div class="orderInfoSaveConf" style="display: none;">
   		<div class="yellowMessage">
   			<div>
   				{t _order_information_was_saved_successfuly}
   			</div>
   		</div>
   	</div>
    
 
    <label for="order_{$order.ID}_user">{t _user}</label>
    <fieldset class="error">
        {textfield name="user" id="order_`$order.ID`_user"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    <label for="order_{$order.ID}_email">{t _email}</label>
    <fieldset class="error">
        {textfield name="email" id="order_`$order.ID`_email"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    <label for="order_{$order.ID}_shippingAddress1">{t _shipping_address}</label>
    <fieldset class="error">
        {textfield name="shippingAddress1" id="order_`$order.ID`_shippingAddress1"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    <label for="order_{$order.ID}_shippingAddress2">{t _shipping_address}</label>
    <fieldset class="error">
        {textfield name="shippingAddress2" id="order_`$order.ID`_shippingAddress2"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>

    
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