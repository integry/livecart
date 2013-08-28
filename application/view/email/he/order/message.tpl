הודעה חדשה שמיוחסת להזמנה שלך ב [[ config('STORE_NAME') ]]
לכבוד [[user.fullName]],

הודעה חדשה בדבר ההזמנה שלך התקבלה.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

תוכל להגיב להודעה זו על ידי לחיצה על הקישור שלהלן:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}