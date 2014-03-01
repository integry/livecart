הזמנה חדשה נמצאת ב [[ config('STORE_NAME') ]]
מספר הזמנה: [[order.invoiceNumber]]

ניהול הזמנה:
{backendOrderUrl order=order url=true}

הפריטים שמופיעים למטה הוזמנו:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]