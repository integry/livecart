{capture assign="labels"}
<table style="width: 100%;">
	{assign var=index value=0}
	{assign var=repeat value='SHIP_LABELS_REPEAT'|config|default:1}
	{assign var=perRow value='SHIP_LABELS_PER_ROW'|config|default:1}
	{assign var=width value="100/$perRow"}
	{foreach from=$feed item=shipment}
		{section name="labels" loop=$repeat}
			{if 0 == $index}
				<tr style="page-break-inside: avoid;">
			{/if}
			{assign var=index value=$index+1}

			<td style="width: [[width]]%; page-break-inside: avoid;">
				{include file="backend/customerOrder/block/shippingLabel.tpl" address=$shipment.ShippingAddress}
			</td>

			{if $perRow == $index}
				</tr>
				{assign var=index value=0}
			{/if}
		{/section}
	{/foreach}
</table>
{/capture}

<body onLoad="window.print()">
{section name="copies" loop='SHIP_LABELS_COPIES'|config|default:1}
	<div style="page-break-after: always;">
		[[labels]]
	</div>
{/section}
</body>