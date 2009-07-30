<tr><td colspan="3">{t _shipping} ({$shipment.ShippingService.name_lang}):</td>
	<td align="right">{$shipment.selectedRate.taxPrice[$order.Currency.ID]|default:$shipment.selectedRate.formattedPrice[$order.Currency.ID]}</td>
</tr>
