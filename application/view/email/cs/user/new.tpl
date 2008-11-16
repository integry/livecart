Vítejte na {'STORE_NAME'|config}!
Vážený(á) {$user.fullName},

zasíláme Vám přihlašovací údaje do našeho obchodu {'STORE_NAME'|config}:

E-mail: {$user.email}
Heslo: {$user.newPassword}

Zde můžete sledovat stav a historii Vašich objednávek, stahovat objednané soubory, měnit kontaktní údaje, atd.

Pro přihlášení k Vašemu účtu můžete použít tento odkaz:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}