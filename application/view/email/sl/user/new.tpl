Pozdravljeni na [[ config('STORE_NAME') ]]!
Spoštovani/a [[user.fullName]],

Vaši podatki za spletno trgovino [[ config('STORE_NAME') ]]:

E-mail: <b>[[user.email]]</b>
Geslo: <b>[[user.newPassword]]</b>

Z vaše nadzorne strani, lahko preverjate status vašega naročila, pogledate vaša prejšnja naročila in spremenite vaše kontaktne informacije.

Za prijavo lahko sledite spodnji povezavi:
{link controller=user action=login url=true}

[[ partial("email/en/signature.tpl") ]]