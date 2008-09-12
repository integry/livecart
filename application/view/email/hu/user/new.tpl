Üdv a  {'STORE_NAME'|config} virtuális üzletben!
Kedves {$user.fullName},

Az alábiakban találod a bejelentkezési információkat a {'STORE_NAME'|config} virtuális üzlethez:

E-mail: {$user.email}
Jelszó: {$user.newPassword}

Felhasználói felületedről láthatod: rendelésed aktuális státuszát, régebbi rendeléseid. Ugyanakkor innen töltheted le a megvásárolt digitális állományokat, és módosíthatod elérhetőséged is.

Ezt a címet használhatod, hogy bejelentkezz felhasználói felületedre:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}