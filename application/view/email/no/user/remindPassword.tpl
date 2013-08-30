Ditt passord hos [[ config('STORE_NAME') ]]!
Kjære [[user.fullName]],

Her er din kontaktinformasjon som kunde hos [[config.STORE_NAME]]:

E-mail: <b>[[user.email]]</b>
Passord: <b>[[user.newPassword]]</b>

Du kan bruke denne linken til å logge eg inn på din kundeside:
[[ fullurl("user/login") ]]

[[ partial("email/no/signature.tpl") ]]