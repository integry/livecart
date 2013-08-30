<table class="quantityPrice table table-striped table-condensed table-hover table-bordered">
	{foreach from=$quantityPricing item=quantityPrice key=quant name=quant}
		<tr>
			<td class="quantityAmount">
				{% if $quantityPrice.to %}
					[[quantityPrice.from]] - [[quantityPrice.to]]
				{% else %}
					{maketext text="_x_or_more" params=$quantityPrice.from}
				{% endif %}
			</td>
			<td class="price quantityPrice">
				[[quantityPrice.formattedPrice]]
			</td>
		</tr>
	{/foreach}
</table>
