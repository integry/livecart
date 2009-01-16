Naujo užsakymo pranešimas iš {'STORE_NAME'|config}
Vartotojas parašė naują žinutę susijusią su užsakymu <b class="orderID">#{$order.ID}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Jūs galite pridėti atsakymą iš Užsakymų valdymo dalies:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/lt/signature.tpl"}