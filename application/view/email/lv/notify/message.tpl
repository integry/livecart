[[ config('STORE_NAME') ]] jauns klienta paziņojums sakarā ar pasūtījumu
Klients ir pievienojis jaunu paziņojumu pie pasūtījuma <b class="orderID">#[[order.invoiceNumber]]</b>

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Jūs varat pievienot atbildi no administrācijas zonas:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/lv/signature.tpl"}