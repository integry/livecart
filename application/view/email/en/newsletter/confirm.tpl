[[ config('STORE_NAME') ]] newsletter e-mail address confirmation
Hello,

To confirm your e-mail address and start receiving our newsletters, please visit this link:
[[ fullurl("newsletter/confirm", email=`$email`&code=`$subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
If you do not want to receive any more newsletter messages from us, please visit the link below to remove yourself from our mailing list:
[[ fullurl("newsletter/unsubscribe", email=`$email`) ]]