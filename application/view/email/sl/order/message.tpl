Novo sporočilo glede vašega naročila na [[ config('STORE_NAME') ]]
Spoštovani/a [[user.fullName]],

Dodano je bilo novo sporočilo glede vašega naročila.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Na to sporočilo lahko odgovorite s naslednje strani:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/en/signature.tpl") ]]