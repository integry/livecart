[[ partial('backend/shippingService/service.tpl', ['service': service]) ]]
<script type=text/javascript>
	var newForm = Backend.DeliveryZone.ShippingService.prototype.getInstance(
		$("shippingService_servicesList_[[service.DeliveryZone.ID]]_[[service.ID]]").down('form'),
		{json array=$service}
	);
</script>