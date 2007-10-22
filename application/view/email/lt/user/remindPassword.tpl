Your password at {'STORE_NAME'|config}!
Gerbiama(-s) {$user.fullName},

Siunčiame Jūsų {$config.STORE_NAME} sąskaitos prieigos informaciją:

El-paštas: {$user.email}
Slaptažodis: {$user.newPassword}

Prisijungti galite šiu adresu:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}