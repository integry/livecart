Your password at [[ config('STORE_NAME') ]]!
Dear [[user.fullName]],

Az alábiakban találod a bejelentkezési információkat a [[config.STORE_NAME]]:

E-mail: <b>[[user.email]]</b>
Jelszó: <b>[[user.newPassword]]</b>

Ezt a címet használhatod, hogy bejelentkezz felhasználói felületedre:
[[ fullurl("user/login") ]]

[[ partial("email/en/signature.tpl") ]]