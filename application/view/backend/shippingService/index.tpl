{* upper menu *}
<fieldset class="container">
	<ul class="menu" id="shippingService_menu_{$deliveryZone.ID}">
	    <li><a href="#new_service" id="shippingService_new_{$deliveryZone.ID}_show">{t _add_new_service}</a></li>
	    <li><a href="#cencel_service" id="shippingService_new_{$deliveryZone.ID}_cancel" class="hidden">{t _cancel_adding_new_service}</a></li>
	</ul>
</fieldset>

{* new form *}
<fieldset id="shippingService_new_service_{$deliveryZone.ID}_form" style="display: none;">
    {include file="backend/shippingService/service.tpl" deliveryZone=$deliveryZone}
</fieldset>


<ul class="activeList activeList_add_delete activeList_add_sort activeList_add_edit shippingService_servicesList" id="shippingService_servicesList_{$deliveryZone.ID}">
{foreach from=$shippingServices item="service"}
    <li id="shippingService_servicesList_{$deliveryZone.ID}_{$service.ID}">
        <span class="shippingService_servicesList_title">{$service.name}</span>
    </li>
{/foreach}
</ul>

{literal}
<script type="text/jscript">
    try
    {
        Event.observe($("shippingService_new_{/literal}{$deliveryZone.ID}{literal}_show"), "click", function(e) 
        {
            Event.stop(e);
            
            var newForm = Backend.DeliveryZone.ShippingService.prototype.getInstance(
                $("shippingService_new_service_{/literal}{$deliveryZone.ID}{literal}_form").down('form'),
                {/literal}{json array=$newService}{literal}
            );
            
            newForm.showNewForm();
        });   
    }
    catch(e)
    {
        console.info(e);
    }

    ActiveList.prototype.getInstance("shippingService_servicesList_{/literal}{$deliveryZone.ID}{literal}", Backend.DeliveryZone.ShippingService.prototype.ServiceCallbacks, function() {});
</script>
{/literal}

{json array=$newRate}
