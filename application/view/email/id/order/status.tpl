{'STORE_NAME'|config} Perubahan Status Order
Yth. Bapak/Ibu {$user.fullName},

{if $order.shipments|@count == 1}
Status order Anda yaitu order #{$order.ID} telah diubah.
{else}
Status dari satu atau beberapa pengiriman order Anda yaitu order #{$order.ID} telah diubah.
{/if}

Jika Anda memiliki pertanyaan seputar order anda, maka Anda dapat mengirimkan e-mail kepada kami atau hubungi kami melalui halaman berikut::
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Status baru: {if $shipment.status == 2}menunggu pengiriman{elseif $shipment.status == 3}terkirim{elseif $shipment.status == 4}dikembalikan{else}sedang disiapkan{/if}

------------------------------------------------------------
Barang						 Harga	 Jumlah   Subtotal
------------------------------------------------------------
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}