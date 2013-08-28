New Message Regarding Your Order at [[ config('STORE_NAME') ]]
Gerbiama(-s) [[user.fullName]],

Jums išsiųsta nauja žinutė susijusi su Jūsų užsakymu.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Jūs galite atsakyti į žinutę iš šio puslapio:
{link controller=user action=viewOrder id=$order.ID url=true}

[[ partial("email/lt/signature.tpl") ]]