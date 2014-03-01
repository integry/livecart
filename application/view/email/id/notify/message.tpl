Pesan Baru Tentang Order di  [[ config('STORE_NAME') ]]
Seorang pelanggan mengirimkan pesan mengenai order <b class="orderID">#[[order.invoiceNumber]]</b>

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Anda dapat memberi respons melalui panel manajemen order:
{backendOrderUrl order=order url=true}#tabOrderCommunication__

[[ partial("email/id/signature.tpl") ]]