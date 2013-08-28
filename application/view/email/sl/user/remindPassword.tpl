Vaše geslo za [[ config('STORE_NAME') ]]!
Spoštovani/a [[user.fullName]],

Vaši podatki za spletno trgovino [[config.STORE_NAME]]:

E-mail: <b>[[user.email]]</b>
Geslo: <b>[[user.newPassword]]</b>

V spletno trgovino se lahko prijavite tukaj:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}