Velkommen til {'butikknavnet_ditt'|config}!
Kjære {$user.fullName},

Her er din kontaktinformasjon som kunde hos {'butikknavnet_ditt'|config}:

E-mail: {$user.email}
Passord: {$user.newPassword}

Fra din kundeside kan du til enhver tid se status på dine bestillinger, se tidligere bestillinger, laste ned filer (kjøpte digitale produkter) og endre din kontaktinformasjon.

Du kan logge deg inn på din kundeside her:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}