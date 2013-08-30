[[ config('STORE_NAME') ]] Mise a jour du statut de la commande
Cher [[user.fullName]],

{% if $order.shipments|@count == 1 %}
Le statut de votre commande a été mis a jour <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
Le statut a été mis a jour pour une ou plusieures livraisons de votre commande <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Si vous avez des questions a propos de cette commande, vous pouvez nous envoyer un courriel ou nous contacter a parti de la page suivante:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$shipments item=shipment}
Nouveau statut: {% if $shipment.status == 2 %}awaiting shipment{% elseif $shipment.status == 3 %}shipped{% elseif $shipment.status == 4 %}returned{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/fr/signature.tpl") ]]