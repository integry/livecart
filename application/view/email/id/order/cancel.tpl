{'STORE_NAME'|config} Order Dibatalkan
Yth. Bapak/Ibu {$user.fullName},

Order Anda #{$order.ID}, di {'STORE_NAME'|config}, telah dibatalkan.

Jika Anda memiliki pertanyaan seputar order anda, maka Anda dapat mengirimkan e-mail kepada kami atau hubungi kami melalui halaman berikut:
{link controller=user action=viewOrder id=$order.ID url=true}

Barang pada order yang dibatalkan:
------------------------------------------------------------
Barang						 Harga	 Jumlah   Subtotal
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------

{include file="email/en/signature.tpl"}