Naujo užsakymo pranešimas iš [[ config('STORE_NAME') ]]
Vartotojas parašė naują žinutę susijusią su užsakymu <b class="orderID">#{$order.invoiceNumber}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Jūs galite pridėti atsakymą iš Užsakymų valdymo dalies:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/lt/signature.tpl"}