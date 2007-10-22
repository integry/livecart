Laipni lūdzam {'STORE_NAME'|config}!
Cien. {$user.fullName},

Jūsu lietotāja konta pieejas informācija {'STORE_NAME'|config} ir sekojoša:

E-pasts: {$user.email}
Parole: {$user.newPassword}

No Jūsu lietotāja konta, varat sekot līdzi pasūtījuma statusam, apskatīt iepriekšējos pasūtījumus, lejupielādēt failus un nomainīt savu kontaktinformāciju.

Pieslēgties savam lietotāja kontam varat no šejienes:
{link controller=user action=login url=true}

{include file="email/lv/signature.tpl"}