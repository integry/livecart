Your password at [[ config('STORE_NAME') ]]!
Gerbiama(-s) [[user.fullName]],

Siunčiame Jūsų [[config.STORE_NAME]] sąskaitos prieigos informaciją:

El-paštas: <b>[[user.email]]</b>
Slaptažodis: <b>[[user.newPassword]]</b>

Prisijungti galite šiu adresu:
[[ fullurl("user/login") ]]

[[ partial("email/lt/signature.tpl") ]]