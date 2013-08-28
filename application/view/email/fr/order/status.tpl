[[ config('STORE_NAME') ]] Mise a jour du statut de la commande
Cher [[user.fullName]],

{if $order.shipments|@count == 1}
Le statut de votre commande a été mis a jour <b class="orderID">#[[order.invoiceNumber]]</b>.
{else}
Le statut a été mis a jour pour une ou plusieures livraisons de votre commande <b class="orderID">#[[order.invoiceNumber]]</b>.
{/if}

Si vous avez des questions a propos de cette commande, vous pouvez nous envoyer un courriel ou nous contacter a parti de la page suivante:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Nouveau statut: {if $shipment.status == 2}awaiting shipment{elseif $shipment.status == 3}shipped{elseif $shipment.status == 4}returned{else}processing{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/fr/signature.tpl"}