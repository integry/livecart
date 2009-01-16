Password Anda di {'STORE_NAME'|config}!
Yth. Bapak/Ibu {$user.fullName},

Berikut ini adalah informasi rekening Anda di {$config.STORE_NAME}:

E-mail: <b>{$user.email}</b>
Password: <b>{$user.newPassword}</b>

Anda dapat login ke rekening Anda di:
{link controller=user action=login url=true}

{include file="email/id/signature.tpl"}