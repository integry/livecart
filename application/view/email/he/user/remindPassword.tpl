הסיסמא שלך ב {'STORE_NAME'|config}!
לכבוד {$user.fullName},

כאן כלקוח אתה מקבל את נתוני הגישה ב {$config.STORE_NAME}:

דוא''ל: {$user.email}
סיסמתך: {$user.newPassword}

על מנת להתחבר לחשבון שלך , השתמש בכתובת שלהלן:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}