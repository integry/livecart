{'STORE_NAME'|config} ההזמנה בוטלה
לכבוד {$user.fullName},

ההזמנה שלך <b class="orderID">#{$order.ID}</b>, שממוקמת ב {'STORE_NAME'|config}, בוטלה.

אם יש לך איזושהי שאלה שמתייחסת להזמנה זו, תוכל תמיד לשלוח הודעה באי-מייל או דרך טופס יצירת הקשר שנמצא בלינק שלהלן:
{link controller=user action=viewOrder id=$order.ID url=true}

הפריטים שבוטלו בהזמנה:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}