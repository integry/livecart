{'STORE_NAME'|config} pasūtījuma apstiprinājums
Cien. {$user.fullName},

Paldies par Jūsu {'STORE_NAME'|config} pasūtījumu. Ja nepieciešams griezties pie mums dēļ papildus informācijas sakarā ar šo pasūtījumus, lūdzu miniet pasūtījuma numuru <b class="orderID">#{$order.invoiceNumber}</b>.

Jūs varat sekot līdzi pasūtījuma izpildes gaitai no šīs lapas:
{link controller=user action=viewOrder id=$order.ID url=true}

Ja radušies kādi jautājumi sakarā ar pasūtījumu, no šīs pašas lapas varat tos mums arī nosūtīt.

Atgādinām, ka pasūtījāt sekojošos produktus:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/lv/signature.tpl"}