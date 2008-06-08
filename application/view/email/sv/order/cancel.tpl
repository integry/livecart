{'STORE_NAME'|config} Order anullerad
Kära {$user.fullName},

Din order #{$order.ID}, hos {'STORE_NAME'|config}, har annullerats.

Om du har några frågor om ordern kan du sända oss ett e-mail eller kontakta oss via följande länk:
{link controller=user action=viewOrder id=$order.ID url=true}

Den annullerade ordern innehöll följande varor:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}