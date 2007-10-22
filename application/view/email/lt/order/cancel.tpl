{'STORE_NAME'|config} Order Cancelled
Gerbiama(-s) {$user.fullName},

Jūsų užsakymas #{$order.ID}, kurį pildėte {'STORE_NAME'|config}, buvo atšauktas.

Jei turite klausimų susijusių su šiuo užsakymu, galite siųsti mums laišką adresu arba susisiekti iš šio puslapio:
{link controller=user action=viewOrder id=$order.ID url=true}

Prekės, kurias užsisakėte:
------------------------------------------------------------
Prekė                      Kaina     Kiekis     Tarpinė suma
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------

{include file="email/en/signature.tpl"}