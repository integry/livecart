ברוכים הבאים אל {'STORE_NAME'|config}!
לכבוד {$user.fullName},

אתה כלקוח מקבל כעת מידע וגישות כניסה ל {'STORE_NAME'|config}:

כתובת הדוא''ל: <b>{$user.email}</b>
סיסמתך: <b>{$user.newPassword}</b>

מחשבון הלקוח שלך אתה יכול בכל רגע נתון לראות את מצב ההזמנה שלך, לצפות בהזמנות אחרונות ולהוריד קבצי מידע (עבור פריטים דיגיטלים שנרכשו) ולשנות את נתוני יצירת הקשר

עליך להשתמש בכתובת הבאה על מנת להתחבר לחשבון שלך:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}