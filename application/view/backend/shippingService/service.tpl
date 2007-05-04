{form id="shippingService_`$deliveryZone.ID`_`$service.ID`" handle=$form action="controller=backend.deliveryZone action=save id=`$deliveryZone.ID`" method="post"}
    <label>{t _name}</label>
    <fieldset class="error">
		{textfield name="name_`$defaultLanguageCode`" class="observed shippingService_name" }
		<span class="errorText hidden"> </span>
    </fieldset>
    
    
	<fieldset class="error">
		<label for=""></label>
		{radio name="rangeType" id="shippingService_`$deliveryZone.ID`_`$service.ID`" class="checkbox shippingService_rangeType" value="0"}
		<label for="shippingService_{$deliveryZone.ID}_{$service.ID}" class="checkbox">{t _weight_based_calculations}</label>
	</fieldset class="error">
	<fieldset class="error">
		<label for=""></label>
		{radio name="rangeType" id="shippingService_`$deliveryZone.ID`_`$service.ID`" class="checkbox shippingService_rangeType" value="1"}
		<label for="shippingService_{$deliveryZone.ID}_{$service.ID}" class="checkbox">{t _subtotal_based_calculations}</label>
	</fieldset class="error">
    
    
    <fieldset>
    {foreach from=$alternativeLanguagesCodes key=lang item=langName}
        <fieldset class="expandingSection">
            <legend>Translate to: {$langName}</legend>
            <div class="expandingSectionContent">
                <label>{t _name}</label>
                <fieldset class="error">
                    {textfield name="name_`$lang`" class="observed"}
                    <span class="errorText hidden"> </span>
                </fieldset>
            </div>
        </fieldset>
    {/foreach}
    </fieldset>
 
    <fieldset class="shippingService_rates error">
    	<label>{t _shipping_service_rates}</label>
        <fieldset>
            <ul class="activeList activeList_add_delete shippingService_ratesList" id="shippingService_ratesList_{$deliveryZone.ID}_{$service.ID}">
                {foreach from=$shippingRates item="rate"}
                    <li id="shippingService_ratesList_{$deliveryZone.ID}_{$service.ID}_{$rate.ID}">
                        {include file="backend/shippingService/rate.tpl" rate=$rate}
                    </li>
                {/foreach}
            </ul>
            
            <fieldset class="container">
            	<ul class="menu" id="shippingService_rate_menu_{$deliveryZone.ID}_{$service.ID}">
            	    <li><a href="#new_rate" id="shippingService_new_rate_{$deliveryZone.ID}_{$service.ID}_show">{t _add_new_rate}</a></li>
            	    <li><a href="#cencel_rate" id="shippingService_new_rate_{$deliveryZone.ID}_{$service.ID}_cancel" class="hidden">{t _cancel_adding_new_rate}</a></li>
            	</ul>
            </fieldset>
            <fieldset id="shippingService_new_rate_{$deliveryZone.ID}_{$service.ID}_form" style="display: none;">
                {include file="backend/shippingService/rate.tpl" rate=$newRate}
            </fieldset>
            
            <script type="text/jscript">
                {literal}
                try
                {
                    Event.observe($("shippingService_new_rate_{/literal}{$deliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}_show"), "click", function(e) 
                    {
                        Event.stop(e);
                        var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(
                            $("shippingService_new_rate_{/literal}{$deliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}_form"),
                            {/literal}{json array=$newRate}{literal}
                        );
                        
                        newForm.showNewForm();
                    });   
                
                    ActiveList.prototype.getInstance("shippingService_ratesList_{/literal}{$deliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}", Backend.DeliveryZone.ShippingRate.prototype.Callbacks, function() {});
                }
                catch(e)
                {
                    console.info(e);
                }
                
                {/literal}
                
            </script>
        </fieldset>
        
        <fieldset class="shippingService_controls">
            <span class="activeForm_progress"></span>
            <input type="submit" class="shippingService_save button submit" value="{t _save}" />
            {t _or}
            <a href="#cancel" class="shippingService_cancel cancel">{t _cancel}</a>
        </fieldset>
    </fieldset>


    <script type="text/javascript">
       // Backend.DeliveryZone.ShippingService.prototype.getInstance('shippingService_{$deliveryZone.ID}_{$service.ID}', {$deliveryZone.ID});
    </script>
{/form}