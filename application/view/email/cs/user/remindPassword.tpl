Vaše heslo na [[ config('STORE_NAME') ]]!
Vážený(á) [[user.fullName]],

zasíláme Vám přihlašovací údaje do našeho obchodu [[config.STORE_NAME]]:

E-mail: <b>[[user.email]]</b>
Heslo: <b>[[user.newPassword]]</b>

Pro přihlášení k Vašemu účtu můžete použít tento odkaz:
[[ fullurl("user/login") ]]

[[ partial("email/en/signature.tpl") ]]