Új üzenet a [[ config('STORE_NAME') ]] leadott rendeléseddel kapcsolatosan
Tisztelt [[user.fullName]],

Új üzenet lett hozzáadva rendeléseddel kapcsolatosan.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Válaszolhatsz erre az üzenetre a következő linkre kattintva:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/en/signature.tpl") ]]