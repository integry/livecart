Ny beskjed ang. din betilling hos {'STORE_NAME'|config}
Kjære {$user.fullName},

En ny beskjed er lagt til ang. din bestilling.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Du kan svare på denne beskjeden på følgende link:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}