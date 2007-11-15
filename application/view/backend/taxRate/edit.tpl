{include file="backend/taxRate/rate.tpl" rate=$taxRate}
<script type=text/javascript>
	{literal}
	var newForm = Backend.DeliveryZone.TaxRate.prototype.getInstance(
		$("{/literal}taxRate_taxRatesList_{$taxRate.DeliveryZone.ID}_{$taxRate.ID}{literal}").down('form'),
		{/literal}{json array=$taxRate}{literal}
	);
	{/literal}
</script>