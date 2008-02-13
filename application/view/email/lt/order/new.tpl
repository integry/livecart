{'STORE_NAME'|config} Order Confirmation
Gerbiama(-s) {$user.fullName},

Dėkojame už Jūsų užsakymą iš {'STORE_NAME'|config}. Jei Jums reikia su mumis susisiekti, prašome naudoti šį užsakymo ID: #{$order.ID}.

Jūs galite sekti savo užsakymo būseną šiame puslapyje:
{link controller=user action=viewOrder id=$order.ID url=true}

Jei turite klausimų susijusių su užsakymu galite rašyti žinutę taip pat ir šio puslapio aukščiau.

Primename, kad užsisakėte šias prekes:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}