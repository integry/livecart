Your password at {'STORE_NAME'|config}!
Dear {$user.fullName},

Az alábiakban találod a bejelentkezési információkat a {$config.STORE_NAME}:

E-mail: <b>{$user.email}</b>
Jelszó: <b>{$user.newPassword}</b>

Ezt a címet használhatod, hogy bejelentkezz felhasználói felületedre:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}