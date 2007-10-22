Jūsu {$config.STORE_NAME} parole
Cien. {$user.fullName},

Jūsu {$config.STORE_NAME} lietotāja konta pieejas informācija:

E-pasts: {$user.email}
Parole: {$user.newPassword}

Piekļūt savam lietotāja kontam varat no šejienes:
{link controller=user action=login url=true}

{include file="email/lv/signature.tpl"}