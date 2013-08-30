[[ config('STORE_NAME') ]] Rendelés megerősítése
Tisztelt [[user.fullName]],

Köszönjük rendelésedet, amelyet most adtál le a [[ config('STORE_NAME') ]]virtuális üzletében. Ha kapcsolatba szeretnél lépni velünk a rendelés miatt, kérünk a következő rendelési azonosítót jegyezd meg: <b class="orderID">#[[order.invoiceNumber]]</b>.

Rendelésed nyomon követhető a következő oldalon:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Ha bármilyen kérdésed van a rendeléssel kapcsolatosan, azt szintén felteheted a fenti oldalon.

A következő termékeket rendelted meg:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]