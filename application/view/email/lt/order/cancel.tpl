[[ config('STORE_NAME') ]] užsakymas atšauktas
Gerbiama(-s) [[user.fullName]],

Jūsų užsakymas <b class="orderID">#[[order.invoiceNumber]]</b>, kurį pildėte [[ config('STORE_NAME') ]], buvo atšauktas.

Jei turite klausimų susijusių su šiuo užsakymu, galite siųsti mums laišką adresu arba susisiekti iš šio puslapio:
{link controller=user action=viewOrder id=$order.ID url=true}

Prekės, kurias užsisakėte:
{include file="email/blockOrderItems.tpl"}

{include file="email/lt/signature.tpl"}