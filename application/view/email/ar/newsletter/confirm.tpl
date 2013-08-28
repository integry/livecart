[[ config('STORE_NAME') ]] تأكيد عنوان البريد الإلكتروني للنشرة
مرحبا ،

لتأكيد عنوان البريد الإلكتروني الخاص بك والبدء في تلقي الرسائل الإخبارية ، يرجى زيارة الرابط التالي :
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
إذا كنت لا تريد أن تتلقى أية رسائل إخبارية أكثر منا ، يرجى زيارة الرابط أدناه لإزالة نفسك من قائمة مراسلاتنا :
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}