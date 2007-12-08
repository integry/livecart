{'STORE_NAME'|config} Konfirmasi Order
Yth Bapak/Ibu {$user.fullName},

Terima kasih atas order Anda, yang baru saja Anda buat di {'STORE_NAME'|config}. Jika Anda hendak menghubungi kami terkait dengan order ini, mohon cantumkan nomor order: #{$order.ID}.

Anda dapat melacak order Anda di halaman ini:
{link controller=user action=viewOrder id=$order.ID url=true}

Jika Anda memiliki pertanyaan seputar order ini, Anda dapat mengirimkan pesan kepada kami di halaman tersebut diatas pula.

Berikut adalah barang yang Anda order dari kami:
------------------------------------------------------------
Barang                         Harga     Jumlah   Subtotal
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------
                                        Subtotal: {$order.formatted_itemSubtotal}
{if $order.shippingSubtotal}
                                Biaya Pengiriman: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes|@count > 1}
                           ---------------------------------
                          Subtotal sebelum pajak: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
                                {$tax.name_lang}: {$tax.formattedAmount}
{/foreach}
{/if}
                           ---------------------------------
                               Total keseluruhan: {$order.formatted_totalAmount}

                                  Jumlah dibayar: {$order.formatted_amountPaid}
                                 Jumlah terutang: {$order.formatted_amountDue}

{* Function to generate address output (address template) *}
{defun name="address"}
{if $address}
{$address.fullName}                
{if $address.companyName}
{$address.companyName}

{/if}
{if $address.address1}
{$address.address1}

{/if}
{if $address.address2}
{$address.address2}

{/if}
{$address.city}
{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
{$address.countryName}
{/if}
{/defun}

{if $order.BillingAddress}
Order ditagihkan kepada:
{fun name="address" address=$order.BillingAddress}
{/if}

{if $order.ShippingAddress}
Order akan dikirimkan kepada:
{fun name="address" address=$order.ShippingAddress}
{/if}

{include file="email/en/signature.tpl"}