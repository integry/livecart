تم  عمل طلب جديد في... {'STORE_NAME'|config}... 
تم و عمل طلب جديد في... {'STORE_NAME'|config}... 

معرف الطلب: {$order.invoiceNumber}

إدارة الطلب:
{backendOrderUrl order=$order url=true}

طلبت البنود التالية :
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}