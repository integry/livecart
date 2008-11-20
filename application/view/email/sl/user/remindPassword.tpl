Vaše geslo za {'STORE_NAME'|config}!
Spoštovani/a {$user.fullName},

Vaši podatki za spletno trgovino {$config.STORE_NAME}:

E-mail: {$user.email}
Geslo: {$user.newPassword}

V spletno trgovino se lahko prijavite tukaj:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}