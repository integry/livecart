{if !$html}
{foreach from=$shipment.items item=item}

{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad_iconv:31}{$item.formattedDisplayPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedDisplaySubTotal}
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
{if !$noTable}</table>{/if}
{/if}{*html*}