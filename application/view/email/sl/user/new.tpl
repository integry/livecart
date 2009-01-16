Pozdravljeni na {'STORE_NAME'|config}!
Spoštovani/a {$user.fullName},

Vaši podatki za spletno trgovino {'STORE_NAME'|config}:

E-mail: <b>{$user.email}</b>
Geslo: <b>{$user.newPassword}</b>

Z vaše nadzorne strani, lahko preverjate status vašega naročila, pogledate vaša prejšnja naročila in spremenite vaše kontaktne informacije.

Za prijavo lahko sledite spodnji povezavi:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}