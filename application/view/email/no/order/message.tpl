Ny beskjed ang. din betilling hos [[ config('STORE_NAME') ]]
Kjære [[user.fullName]],

En ny beskjed er lagt til ang. din bestilling.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan svare på denne beskjeden på følgende link:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/no/signature.tpl") ]]