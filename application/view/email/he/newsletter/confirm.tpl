אימות קבלת עדכונים מ - [[ config('STORE_NAME') ]]
היי,

כדי לאמת את כתובת האי-מייל ולהתחיל לקבל עדכונים מעלון המידע שלנו, אנא בקשר בקישור הבא:
[[ fullurl("newsletter/confirm", email=`email`&code=`subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
אם אינך רוצה לקבל עוד עדכונים והודעות שונות מעלון המידע שלנו, אנא לחץ על הלינק שלמטה על מנת להסיר את עצמך מרשימת הדיוור
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]