{'butikknavnet_ditt'|config} Oppdatering av status på bestilling
Kjære {$user.fullName},

{if $order.shipments|@count == 1}
Status er oppdatert for bestillingsnr.{$order.ID}.
{else}
Status er oppdatert for en eller flere forsendelser av din bestillingsordre #{$order.ID}.
{/if}

Hvis du har spørsmål ang. denne bestillingen, kan du sende oss en mail eller kontakte oss på følgene link:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Ny status: {if $shipment.status == 2}Forventet levering{elseif $shipment.status == 3}sendt{elseif $shipment.status == 4}returnert{else}under behandling {/if}

------------------------------------------------------------
Produkt                           Pris     Antall      Sum
------------------------------------------------------------
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}