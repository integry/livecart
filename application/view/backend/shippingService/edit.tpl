{include file="backend/shippingService/service.tpl" service=$service}
<script type=text/javascript>
    console.info($("shippingService_servicesList_{$service.DeliveryZone.ID}_{$service.ID}").down('form'));
    console.info({json array=$service});
    var newForm = Backend.DeliveryZone.ShippingService.prototype.getInstance(
        $("shippingService_servicesList_{$service.DeliveryZone.ID}_{$service.ID}").down('form'),
        {json array=$service}
    );
</script>