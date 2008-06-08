Välkommen till {'STORE_NAME'|config}!
Kära {$user.fullName},

Här kommer dina användaruppgifter hos {'STORE_NAME'|config}:

E-mail: {$user.email}
Password: {$user.newPassword}

Via ditt användarkonto kan du se status på din order, tidigare order, ladda ner filer (för nedladdningsbara produkter du köpt) och uppdatera din kontaktinformation.

Du kan logga in på ditt konto via den här länken:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}