[[ config('STORE_NAME') ]] אימות הזמנה
לכבוד [[user.fullName]],

תודה רבה על שהזמנת מחנות [[ config('STORE_NAME') ]] את המוצר. צור איתנו קשר בכל עת אפשרית בנוגע לבעיות הנוגעות להזמנה, אנא דאג לכלול את מספר ההזמנה ביחד עם שליחת ההודעה <b class="orderID">#[[order.invoiceNumber]]</b>.

תוכל לעקוב אחר תהליך ההזמנה שלך בעמוד שלמטה:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

אם יש לך שאלות כלשהם הנוגעות להזמנה זו, אנא שלח הודעה ונדאג לטפל בה בהתאם.

אנו מזכירים לך שהפריטים שהזמנת הם::
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]