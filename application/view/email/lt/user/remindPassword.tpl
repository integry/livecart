Your password at {'STORE_NAME'|config}!
Gerbiama(-s) {$user.fullName},

Siunčiame Jūsų {$config.STORE_NAME} sąskaitos prieigos informaciją:

El-paštas: <b>{$user.email}</b>
Slaptažodis: <b>{$user.newPassword}</b>

Prisijungti galite šiu adresu:
{link controller=user action=login url=true}

{include file="email/lt/signature.tpl"}