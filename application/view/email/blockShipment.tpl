{foreach from=$shipment.items item=item}

{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{if $item.options}
{foreach from=$item.options item=option}
{$option.Choice.Option.name_lang}: {if 0 == $option.Choice.Option.type}{t _option_yes}{elseif 1 == $option.Choice.Option.type}{$option.Choice.name_lang}{else}{$option.optionText|@htmlspecialchars}{/if} {if $option.priceDiff != 0}({$option.formattedPrice}){/if}

{/foreach}

{/if}
{/foreach}