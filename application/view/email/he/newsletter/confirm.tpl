אימות קבלת עדכונים מ - {'STORE_NAME'|config}
היי,

כדי לאמת את כתובת האי-מייל ולהתחיל לקבל עדכונים מעלון המידע שלנו, אנא בקשר בקישור הבא:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

{include file="email/en/signature.tpl"}

-----------------------------------------------
אם אינך רוצה לקבל עוד עדכונים והודעות שונות מעלון המידע שלנו, אנא לחץ על הלינק שלמטה על מנת להסיר את עצמך מרשימת הדיוור
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}