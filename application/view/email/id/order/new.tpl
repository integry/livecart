[[ config('STORE_NAME') ]] Konfirmasi Order
Yth Bapak/Ibu [[user.fullName]],

Terima kasih atas order Anda, yang baru saja Anda buat di [[ config('STORE_NAME') ]]. Jika Anda hendak menghubungi kami terkait dengan order ini, mohon cantumkan nomor order: <b class="orderID">#[[order.invoiceNumber]]</b>.

Anda dapat melacak order Anda di halaman ini:
{link controller=user action=viewOrder id=$order.ID url=true}

Jika Anda memiliki pertanyaan seputar order ini, Anda dapat mengirimkan pesan kepada kami di halaman tersebut diatas pula.

Berikut adalah barang yang Anda order dari kami:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/id/signature.tpl"}