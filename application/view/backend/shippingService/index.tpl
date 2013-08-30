{* upper menu *}
<fieldset class="container" {denied role='delivery.update'}style="display: none;"{/denied}>
	<ul class="menu" id="shippingService_menu_[[deliveryZone.ID]]">
		<li class="shippingService_add"><a href="#new_service" id="shippingService_new_[[deliveryZone.ID]]_show">{t _add_new_shipping_service}</a></li>
		<li class="done shippingService_addCancel" style="display: none"><a href="#cancel_service" id="shippingService_new_[[deliveryZone.ID]]_cancel">{t _cancel_adding_new_service}</a></li>
	</ul>
</fieldset>

{* new form *}
<fieldset class="addForm" id="shippingService_new_service_[[deliveryZone.ID]]_form" style="display: none;">
	<legend>[[ capitalize({t _add_new_shipping_service}) ]]</legend>
	[[ partial('backend/shippingService/service.tpl', ['service': newService]) ]]
</fieldset>


<ul class="activeList {allowed role='delivery.update'}activeList_add_delete{/allowed} activeList_add_sort activeList_add_edit shippingService_servicesList" id="shippingService_servicesList_[[deliveryZone.ID]]">
{foreach from=$shippingServices item="service"}
	<li id="shippingService_servicesList_[[deliveryZone.ID]]_[[service.ID]]">
		<span class="shippingService_servicesList_title">[[service.name]] (<b class="ratesCount">[[service.ratesCount]]</b> [[service.rangeTypeString]])</span>
	</li>
{/foreach}
</ul>


<script type="text/jscript">

	Backend.DeliveryZone.prototype.Messages.weightBasedRates = '{t _weight_based_rates}';
	Backend.DeliveryZone.prototype.Messages.subtotalBasedRates = '{t _subtotal_based_rates}';

	Event.observe($("shippingService_new_[[deliveryZone.ID]]_show"), "click", function(e)
	{
		e.preventDefault();

		var newForm = Backend.DeliveryZone.ShippingService.prototype.getInstance(
			$("shippingService_new_service_[[deliveryZone.ID]]_form").down('form'),
			{json array=$newService}
		);

		newForm.showNewForm();
	});

	ActiveList.prototype.getInstance("shippingService_servicesList_[[deliveryZone.ID]]", Backend.DeliveryZone.ShippingService.prototype.Callbacks, function() {});
</script>

