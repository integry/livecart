{* upper menu *}
<fieldset class="container" {denied role='delivery.update'}style="display: none;"{/denied}>
	<ul class="menu" id="shippingService_menu_{$deliveryZone.ID}">
	    <li class="shippingService_add"><a href="#new_service" id="shippingService_new_{$deliveryZone.ID}_show">{t _add_new_shipping_service}</a></li>
	    <li class="done"><a href="#cencel_service" id="shippingService_new_{$deliveryZone.ID}_cancel" class="hidden">{t _cancel_adding_new_service}</a></li>
	</ul>
</fieldset>

{* new form *}
<fieldset class="addForm" id="shippingService_new_service_{$deliveryZone.ID}_form" style="display: none;">
	<legend>{t _add_new_shipping_service}</legend>
    {include file="backend/shippingService/service.tpl" service=$newService}
</fieldset>


<ul class="activeList {allowed role='delivery.update'}activeList_add_delete{/allowed} activeList_add_sort activeList_add_edit shippingService_servicesList" id="shippingService_servicesList_{$deliveryZone.ID}">
{foreach from=$shippingServices item="service"}
    <li id="shippingService_servicesList_{$deliveryZone.ID}_{$service.ID}">
        <span class="shippingService_servicesList_title">{$service.name} (<b class="ratesCount">{$service.ratesCount}</b> {$service.rangeTypeString})</span>
    </li>
{/foreach}
</ul>

{literal}
<script type="text/jscript">

    Backend.DeliveryZone.TaxRate.prototype.Messages.weightBasedRates = '{/literal}_weight_based_rates{literal}';
    Backend.DeliveryZone.TaxRate.prototype.Messages.subtotalBasedRates = '{/literal}_subtotal_based_rates{literal}';

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

    ActiveList.prototype.getInstance("shippingService_servicesList_{/literal}{$deliveryZone.ID}{literal}", Backend.DeliveryZone.ShippingService.prototype.Callbacks, function() {});
</script>
{/literal}
