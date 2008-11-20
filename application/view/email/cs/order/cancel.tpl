{'STORE_NAME'|config} Objednávka zrušena
Vážený {$user.fullName},

Vaše objednávka č.: {$order.ID} na {'STORE_NAME'|config} byla zrušena.

Pokud k této objednávce máte nějaký dotaz, můžete nam poslat email nebo použít tento odkaz:
{link controller=user action=viewOrder id=$order.ID url=true}

Položky zrušené objednávky:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}