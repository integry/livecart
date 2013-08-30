הסיסמא שלך ב [[ config('STORE_NAME') ]]!
לכבוד [[user.fullName]],

כאן כלקוח אתה מקבל את נתוני הגישה ב [[config.STORE_NAME]]:

דוא''ל: <b>[[user.email]]</b>
סיסמתך: <b>[[user.newPassword]]</b>

על מנת להתחבר לחשבון שלך , השתמש בכתובת שלהלן:
[[ fullurl("user/login") ]]

[[ partial("email/en/signature.tpl") ]]