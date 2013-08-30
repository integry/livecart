Password Anda di [[ config('STORE_NAME') ]]!
Yth. Bapak/Ibu [[user.fullName]],

Berikut ini adalah informasi rekening Anda di [[config.STORE_NAME]]:

E-mail: <b>[[user.email]]</b>
Password: <b>[[user.newPassword]]</b>

Anda dapat login ke rekening Anda di:
[[ fullurl("user/login") ]]

[[ partial("email/id/signature.tpl") ]]