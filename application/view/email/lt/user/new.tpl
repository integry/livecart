Welcome to [[ config('STORE_NAME') ]]!
Gerbiama(-s) [[user.fullName]],

Siunčiame jums [[ config('STORE_NAME') ]] prisijungimo informaciją:

El-paštas: <b>[[user.email]]</b>
Slaptažodis: <b>[[user.newPassword]]</b>

Iš savo sąskaitos galite stebėti savo užsakymų būseną, užsakymų archyvą, parsisiųsti rinkmenas ar keisti savo duomenis.

Prisijungti galite šiuo adresu:
[[ fullurl("user/login") ]]

[[ partial("email/lt/signature.tpl") ]]