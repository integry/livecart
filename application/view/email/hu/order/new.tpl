[[ config('STORE_NAME') ]] Rendelés megerősítése
Tisztelt [[user.fullName]],

Köszönjük rendelésedet, amelyet most adtál le a [[ config('STORE_NAME') ]]virtuális üzletében. Ha kapcsolatba szeretnél lépni velünk a rendelés miatt, kérünk a következő rendelési azonosítót jegyezd meg: <b class="orderID">#[[order.invoiceNumber]]</b>.

Rendelésed nyomon követhető a következő oldalon:
{link controller=user action=viewOrder id=$order.ID url=true}

Ha bármilyen kérdésed van a rendeléssel kapcsolatosan, azt szintén felteheted a fenti oldalon.

A következő termékeket rendelted meg:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}