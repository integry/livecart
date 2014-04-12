{capture assign="labels"}
<table style="width: 100%;">
	{assign var=index value=0}
	{assign var=repeat value=config('SHIP_LABELS_PER_ROW')|default:1}
	{assign var=perRow value=config('SHIP_LABELS_PER_ROW')|default:1}
	{assign var=width value="100/perRow"}
	{% for shipment in feed %}
		{section name="labels" loop=repeat}
			{% if 0 == index %}
				<tr style="page-break-inside: avoid;">
			{% endif %}
			{assign var=index value=index+1}

			<td style="width: [[width]]%; page-break-inside: avoid;">
				[[ partial('backend/customerOrder/block/shippingLabel.tpl', ['address': shipment.ShippingAddress]) ]]
			</td>

			{% if perRow == index %}
				</tr>
				{assign var=index value=0}
			{% endif %}
		{/section}
	{% endfor %}
</table>
{/capture}

<body onLoad="window.print()">
{section name="copies" loop=config('SHIP_LABELS_COPIES')|default:1}
	<div style="page-break-after: always;">
		[[labels]]
	</div>
{/section}
</body>