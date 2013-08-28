[[ config('STORE_NAME') ]] Mailing lista - aktivacija e-mail naslova
Pozdravljeni,

Za aktivacijo e-mail naslova, prosimo kliknite na naslednjo povezavo:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
Če ne želite več prejemati obvestil, prosimo kliknite spodnjo povezavo, da se odjavite z naše mailing liste:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}