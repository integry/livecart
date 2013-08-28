Selamat Datang di [[ config('STORE_NAME') ]]!
Yth Bapak/Ibu [[user.fullName]],

Berikut ini adalah informasi rekening Anda di [[ config('STORE_NAME') ]]:

E-mail: <b>[[user.email]]</b>
Password: <b>[[user.newPassword]]</b>

Anda dapat melihat status order Anda, melihat order terdahulu, mendownload file (untuk pembelian berupa file), dan mengubah informasi alamat Anda, dengan cara login ke rekening Anda.

Anda dapat login ke rekening Anda di:
{link controller=user action=login url=true}

[[ partial("email/id/signature.tpl") ]]