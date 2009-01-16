Ditt passord hos {'STORE_NAME'|config}!
Kjære {$user.fullName},

Her er din kontaktinformasjon som kunde hos {$config.STORE_NAME}:

E-mail: <b>{$user.email}</b>
Passord: <b>{$user.newPassword}</b>

Du kan bruke denne linken til å logge eg inn på din kundeside:
{link controller=user action=login url=true}

{include file="email/no/signature.tpl"}