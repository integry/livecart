Vaše heslo na {'STORE_NAME'|config}!
Vážený(á) {$user.fullName},

zasíláme Vám přihlašovací údaje do našeho obchodu {$config.STORE_NAME}:

E-mail: {$user.email}
Heslo: {$user.newPassword}

Pro přihlášení k Vašemu účtu můžete použít tento odkaz:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}