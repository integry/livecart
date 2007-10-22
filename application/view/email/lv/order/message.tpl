Jauns ziņojums sakarā ar Jūsu {'STORE_NAME'|config} pasūtījumu
Cien. {$user.fullName},

Jūsu pasūtījumam pievienots jauns paziņojums.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Jūs varat atbildēt no šīs lapas:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/lv/signature.tpl"}