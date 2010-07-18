{if 'SHOW_SKU_EMAIL'|config}{assign var="SHOW_SKU" value=true}{/if}
{if !$html}
{foreach from=$shipment.items item=item}

{if $SHOW_SKU}{$item.Product.sku|@str_pad_iconv:10}{/if}{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad_iconv:31}{$item.formattedDisplayPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedDisplaySubTotal}
{if $item.options}
{foreach from=$item.options item=option}
{$option.Choice.Option.name_lang}: {if 0 == $option.Choice.Option.type}{t _option_yes}{elseif 1 == $option.Choice.Option.type}{$option.Choice.name_lang}{else}{$option.optionText|@htmlspecialchars}{/if} {if $option.priceDiff != 0}({$option.formattedPrice}){/if}

{/foreach}

{/if}
{/foreach}
{/if}{*html*}
{if $html}

{foreach from=$shipment.items item=item}
<tr>
	{if $SHOW_SKU}
	<td>
		{$item.Product.sku|escape}
	</td>
	{/if}
	<td>
		{$item.Product.name_lang}
		{if $item.options}
			{foreach from=$item.options item=option}
				<small>{$option.Choice.Option.name_lang}: {if 0 == $option.Choice.Option.type}{t _option_yes}{elseif 1 == $option.Choice.Option.type}{$option.Choice.name_lang}{else}{$option.optionText|@htmlspecialchars}{/if} {if $option.priceDiff != 0}({$option.formattedPrice}){/if}</small><br />
			{/foreach}
		{/if}
	</td>
	<td align="center">{$item.formattedDisplayPrice}</td>
	<td align="center">{$item.count}</td>
	<td align="right">{$item.formattedDisplaySubTotal}</td>
</tr>
{/foreach}
{if $shipment.isShippable && $shipment.ShippingService && ($shipment.CustomerOrder.shipments|@count > 1)}
	{include file="email/blockShippingCost.tpl"}
{/if}
{if !$noTable}</table>{/if}
{/if}{*html*}