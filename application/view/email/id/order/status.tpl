[[ config('STORE_NAME') ]] Perubahan Status Order
Yth. Bapak/Ibu [[user.fullName]],

{% if $order.shipments|@count == 1 %}
Status order Anda yaitu order <b class="orderID">#[[order.invoiceNumber]]</b> telah diubah.
{% else %}
Status dari satu atau beberapa pengiriman order Anda yaitu order <b class="orderID">#[[order.invoiceNumber]]</b> telah diubah.
{% endif %}

Jika Anda memiliki pertanyaan seputar order anda, maka Anda dapat mengirimkan e-mail kepada kami atau hubungi kami melalui halaman berikut::
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Status baru: {% if $shipment.status == 2 %}menunggu pengiriman{% elseif $shipment.status == 3 %}terkirim{% elseif $shipment.status == 4 %}dikembalikan{% else %}sedang disiapkan{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/id/signature.tpl") ]]