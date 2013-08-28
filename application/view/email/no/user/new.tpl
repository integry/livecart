Velkommen til [[ config('STORE_NAME') ]]!
Kjære {$user.fullName},

Her er din kontaktinformasjon som kunde hos [[ config('STORE_NAME') ]]:

E-mail: <b>{$user.email}</b>
Passord: <b>{$user.newPassword}</b>

Fra din kundeside kan du til enhver tid se status på dine bestillinger, se tidligere bestillinger, laste ned filer (kjøpte digitale produkter) og endre din kontaktinformasjon.

Du kan logge deg inn på din kundeside her:
{link controller=user action=login url=true}

{include file="email/no/signature.tpl"}