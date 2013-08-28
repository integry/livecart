Jauns ziņojums sakarā ar Jūsu [[ config('STORE_NAME') ]] pasūtījumu
Cien. {$user.fullName},

Jūsu pasūtījumam pievienots jauns paziņojums.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Jūs varat atbildēt no šīs lapas:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/lv/signature.tpl"}