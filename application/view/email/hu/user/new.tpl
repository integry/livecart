Üdv a  [[ config('STORE_NAME') ]] virtuális üzletben!
Kedves {$user.fullName},

Az alábiakban találod a bejelentkezési információkat a [[ config('STORE_NAME') ]] virtuális üzlethez:

E-mail: <b>{$user.email}</b>
Jelszó: <b>{$user.newPassword}</b>

Felhasználói felületedről láthatod: rendelésed aktuális státuszát, régebbi rendeléseid. Ugyanakkor innen töltheted le a megvásárolt digitális állományokat, és módosíthatod elérhetőséged is.

Ezt a címet használhatod, hogy bejelentkezz felhasználói felületedre:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}