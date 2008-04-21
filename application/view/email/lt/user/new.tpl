Welcome to {'STORE_NAME'|config}!
Gerbiama(-s) {$user.fullName},

Siunčiame jums {'STORE_NAME'|config} prisijungimo informaciją:

El-paštas: {$user.email}
Slaptažodis: {$user.newPassword}

Iš savo sąskaitos galite stebėti savo užsakymų būseną, užsakymų archyvą, parsisiųsti rinkmenas ar keisti savo duomenis.

Prisijungti galite šiuo adresu:
{link controller=user action=login url=true}

{include file="email/lt/signature.tpl"}