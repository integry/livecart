[[ config('STORE_NAME') ]] Order Confirmation
Gerbiama(-s) [[user.fullName]],

Dėkojame už Jūsų užsakymą iš [[ config('STORE_NAME') ]]. Jei Jums reikia su mumis susisiekti, prašome naudoti šį užsakymo ID: <b class="orderID">#[[order.invoiceNumber]]</b>.

Jūs galite sekti savo užsakymo būseną šiame puslapyje:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Jei turite klausimų susijusių su užsakymu galite rašyti žinutę taip pat ir šio puslapio aukščiau.

Primename, kad užsisakėte šias prekes:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/lt/signature.tpl") ]]