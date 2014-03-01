<tr><td colspan="{% if config('SHOW_SKU_EMAIL') %}4{% else %}3{% endif %}">{t _shipping} ([[shipment.ShippingService.name()]]):</td>
	<td align="right">{shipment.selectedRate.taxPrice[order.Currency.ID]|default:shipment.selectedRate.formattedPrice[order.Currency.ID]}</td>
</tr>
