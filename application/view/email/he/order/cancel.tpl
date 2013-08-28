[[ config('STORE_NAME') ]] ההזמנה בוטלה
לכבוד [[user.fullName]],

ההזמנה שלך <b class="orderID">#[[order.invoiceNumber]]</b>, שממוקמת ב [[ config('STORE_NAME') ]], בוטלה.

אם יש לך איזושהי שאלה שמתייחסת להזמנה זו, תוכל תמיד לשלוח הודעה באי-מייל או דרך טופס יצירת הקשר שנמצא בלינק שלהלן:
{link controller=user action=viewOrder id=$order.ID url=true}

הפריטים שבוטלו בהזמנה:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]