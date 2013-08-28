[[ config('STORE_NAME') ]] Order Confirmation
Gerbiama(-s) [[user.fullName]],

Dėkojame už Jūsų užsakymą iš [[ config('STORE_NAME') ]]. Jei Jums reikia su mumis susisiekti, prašome naudoti šį užsakymo ID: <b class="orderID">#[[order.invoiceNumber]]</b>.

Jūs galite sekti savo užsakymo būseną šiame puslapyje:
{link controller=user action=viewOrder id=$order.ID url=true}

Jei turite klausimų susijusių su užsakymu galite rašyti žinutę taip pat ir šio puslapio aukščiau.

Primename, kad užsisakėte šias prekes:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/lt/signature.tpl"}