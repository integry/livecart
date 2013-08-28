[[ config('STORE_NAME') ]] Order Dibatalkan
Yth. Bapak/Ibu [[user.fullName]],

Order Anda <b class="orderID">#[[order.invoiceNumber]]</b>, di [[ config('STORE_NAME') ]], telah dibatalkan.

Jika Anda memiliki pertanyaan seputar order anda, maka Anda dapat mengirimkan e-mail kepada kami atau hubungi kami melalui halaman berikut:
{link controller=user action=viewOrder id=$order.ID url=true}

Barang pada order yang dibatalkan:
{include file="email/blockOrderItems.tpl"}

{include file="email/id/signature.tpl"}