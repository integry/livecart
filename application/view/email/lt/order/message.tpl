New Message Regarding Your Order at {'STORE_NAME'|config}
Gerbiama(-s) {$user.fullName},

Jums išsiųsta nauja žinutė susijusi su Jūsų užsakymu.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Jūs galite atsakyti į žinutę iš šio puslapio:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/lt/signature.tpl"}