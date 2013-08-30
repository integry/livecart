[[ config('STORE_NAME') ]] ההזמנה בוטלה
לכבוד [[user.fullName]],

ההזמנה שלך <b class="orderID">#[[order.invoiceNumber]]</b>, שממוקמת ב [[ config('STORE_NAME') ]], בוטלה.

אם יש לך איזושהי שאלה שמתייחסת להזמנה זו, תוכל תמיד לשלוח הודעה באי-מייל או דרך טופס יצירת הקשר שנמצא בלינק שלהלן:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

הפריטים שבוטלו בהזמנה:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]