{'STORE_NAME'|config} Order Status Update
Gerbiama(-s) {$user.fullName},

{if $order.shipments|@count == 1}
Pasikeitė Jūsų užsakymo #{$order.ID} būsena.
{else}
Būsena pakito vienam ar daugiau Jūsų užsakymo #{$order.ID} siuntinių.
{/if}

Jei turite klausimų, susijusių su šiuo užsakymu, galite siųsti laišką ar susisiekti su mumis iš šio puslapio:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Nauja būsena: {if $shipment.status == 2}laukia siuntimo{elseif $shipment.status == 3}išsiųstas{elseif $shipment.status == 4}grąžintas{else}processing{/if}

------------------------------------------------------------
Prekė                      Kaina     Kiekis     Tarpinė suma
------------------------------------------------------------
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}