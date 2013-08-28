הזמנה חדשה נמצאת ב [[ config('STORE_NAME') ]]
מספר הזמנה: {$order.invoiceNumber}

ניהול הזמנה:
{backendOrderUrl order=$order url=true}

הפריטים שמופיעים למטה הוזמנו:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}