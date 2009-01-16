Pesan Baru Tentang Order di  {'STORE_NAME'|config}
Seorang pelanggan mengirimkan pesan mengenai order <b class="orderID">#{$order.ID}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Anda dapat memberi respons melalui panel manajemen order:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/id/signature.tpl"}