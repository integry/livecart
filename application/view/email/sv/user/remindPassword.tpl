Ditt password hos {'STORE_NAME'|config}!
Kära {$user.fullName},

Här kommer dina inloggningsuppgifter hos {$config.STORE_NAME}:

E-mail: {$user.email}
Password: {$user.newPassword}

Du kan logga in direkt via den här länken:
{link controller=user action=login url=true}

{include file="email/sv/signature.tpl"}