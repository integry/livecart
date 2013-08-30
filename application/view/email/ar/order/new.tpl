[[ config('STORE_NAME') ]] تأكيد طلب
العزيز [[user.fullName]],

شكرا لطلبك ، والتي كنت قد وضعت للتو في [[ config('STORE_NAME') ]]. إذا كنت بحاجة إلى الاتصال بنا فيما يتعلق بهذه طلب ، يرجى اقتبس معرف طلب الخاص بطلبك <b class="orderID">#[[order.invoiceNumber]]</b>.

ستكون قادرة على تتبع ما تك في طلبك في هذه الصفحة :
[[ fullurl("user/viewOrder" ~ order.ID) ]]

إذا كان لديك أي أسئلة بخصوص هذا الطلب ، يمكنك أن ترسل لنا رسالة من الصفحة المذكورة أعلاه وكذلك اى اسئلة اخرى

ونذكركم بأن البنود التالية قد طلبتها
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]