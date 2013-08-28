[[ config('STORE_NAME') ]] ألغي الطلب
العزيز[[user.fullName]],

طلبك <b class="orderID">#[[order.invoiceNumber]]</b>, وضعت في [[ config('STORE_NAME') ]], تم إلغائه.

إذا كان لديك أي أسئلة بخصوص هذا الطلب، ويمكنك أن ترسل لنا رسالة عبر البريد الإلكتروني أو الاتصال من الصفحة التالية :
{link controller=user action=viewOrder id=$order.ID url=true}

تم الغاء هذه العناصر
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]