{'STORE_NAME'|config} עדכון מצב הזמנה
לכבוד {$user.fullName},

{if $order.shipments|@count == 1}
עדכון מצב בנוגע להזמנה שלך #{$order.ID}.
{else}
המצב עודכן עבור משלוח אחד או יותר מההזמנה שלך #{$order.ID}.
{/if}

אם יש לך שאלות כלשהם הנוגעות להזמנה זו, אנא אל תהסס לשלוח אלינו אימייל או ליצור עימנו קשר באמצעות הקישור הבא::
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
מצב חדש: {if $shipment.status == 2}ממתין למשלוח{elseif $shipment.status == 3}shipped{elseif $shipment.status == 4}returned{else}בתהליך{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}