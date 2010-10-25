{'STORE_NAME'|config} تأكيد طلب 
العزيز {$user.fullName},

شكرا لطلبك ، والتي كنت قد وضعت للتو في {'STORE_NAME'|config}. إذا كنت بحاجة إلى الاتصال بنا فيما يتعلق بهذه طلب ، يرجى اقتبس معرف طلب الخاص بطلبك <b class="orderID">#{$order.invoiceNumber}</b>.

ستكون قادرة على تتبع ما تك في طلبك في هذه الصفحة :
{link controller=user action=viewOrder id=$order.ID url=true}

إذا كان لديك أي أسئلة بخصوص هذا الطلب ، يمكنك أن ترسل لنا رسالة من الصفحة المذكورة أعلاه وكذلك اى اسئلة اخرى

ونذكركم بأن البنود التالية قد طلبتها
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}