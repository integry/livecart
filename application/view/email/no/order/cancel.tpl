{'STORE_NAME'|config} Bestilling kansellert
Kjære {$user.fullName},

Din bestillingsnr. {$order.invoiceNumber}, fra {'STORE_NAME'|config}, er kansellert.

Hvis du har noen spørmål rundt dette, kan du sende en email eller kontakte oss på denne linken:
{link controller=user action=viewOrder id=$order.ID url=true}

Produkter som er kansellert:
{include file="email/blockOrderItems.tpl"}

{include file="email/no/signature.tpl"}