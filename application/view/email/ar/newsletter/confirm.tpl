[[ config('STORE_NAME') ]] تأكيد عنوان البريد الإلكتروني للنشرة
مرحبا ،

لتأكيد عنوان البريد الإلكتروني الخاص بك والبدء في تلقي الرسائل الإخبارية ، يرجى زيارة الرابط التالي :
[[ fullurl("newsletter/confirm", email=`email`&code=`subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
إذا كنت لا تريد أن تتلقى أية رسائل إخبارية أكثر منا ، يرجى زيارة الرابط أدناه لإزالة نفسك من قائمة مراسلاتنا :
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]