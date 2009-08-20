הודעה על הזמנה חדשה ב {'STORE_NAME'|config}
הלקוח הוסיף הודעה חדשה המתייחסת להזמנה <b class="orderID">#{$order.invoiceNumber}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

תוכל להוסיף תגובה מפאנל הניהול של ההזמנה:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}