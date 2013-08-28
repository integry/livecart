Új üzenet a [[ config('STORE_NAME') ]] leadott rendeléseddel kapcsolatosan
Tisztelt [[user.fullName]],

Új üzenet lett hozzáadva rendeléseddel kapcsolatosan.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Válaszolhatsz erre az üzenetre a következő linkre kattintva:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}