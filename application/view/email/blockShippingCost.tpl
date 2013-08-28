<tr><td colspan="{if 'SHOW_SKU_EMAIL'|config}4{else}3{/if}">{t _shipping} ([[shipment.ShippingService.name_lang]]):</td>
	<td align="right">{$shipment.selectedRate.taxPrice[$order.Currency.ID]|default:$shipment.selectedRate.formattedPrice[$order.Currency.ID]}</td>
</tr>
