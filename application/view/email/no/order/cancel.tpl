{'butikknavnet_ditt'|config} Bestilling kansellert
Kjære {$user.fullName},

Din bestillingsnr. {$order.ID}, fra {'butikknavnet_ditt'|config}, er kansellert.

Hvis du har noen spørmål rundt dette, kan du sende en email eller kontakte oss på denne linken:
{link controller=user action=viewOrder id=$order.ID url=true}

Produkter som er kansellert:
------------------------------------------------------------
Produkt						   Pris	 Antall	  Sum
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------

{include file="email/en/signature.tpl"}