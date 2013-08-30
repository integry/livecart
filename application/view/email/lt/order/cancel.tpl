[[ config('STORE_NAME') ]] užsakymas atšauktas
Gerbiama(-s) [[user.fullName]],

Jūsų užsakymas <b class="orderID">#[[order.invoiceNumber]]</b>, kurį pildėte [[ config('STORE_NAME') ]], buvo atšauktas.

Jei turite klausimų susijusių su šiuo užsakymu, galite siųsti mums laišką adresu arba susisiekti iš šio puslapio:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Prekės, kurias užsisakėte:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/lt/signature.tpl") ]]