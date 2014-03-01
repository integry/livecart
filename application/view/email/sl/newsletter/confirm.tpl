[[ config('STORE_NAME') ]] Mailing lista - aktivacija e-mail naslova
Pozdravljeni,

Za aktivacijo e-mail naslova, prosimo kliknite na naslednjo povezavo:
[[ fullurl("newsletter/confirm", email=`email`&code=`subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
Če ne želite več prejemati obvestil, prosimo kliknite spodnjo povezavo, da se odjavite z naše mailing liste:
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]