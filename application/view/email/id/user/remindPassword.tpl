Password Anda di {'STORE_NAME'|config}!
Yth. Bapak/Ibu {$user.fullName},

Berikut ini adalah informasi rekening Anda di {$config.STORE_NAME}:

E-mail: {$user.email}
Password: {$user.newPassword}

Anda dapat login ke rekening Anda di:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}