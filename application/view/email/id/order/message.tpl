Pesan Baru Tentang Order Anda di [[ config('STORE_NAME') ]]
Yth Bapak/Ibu [[user.fullName]],

Ada pesan baru mengenai order Anda.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Anda dapat memberikan respons dari halaman berikut:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/id/signature.tpl") ]]