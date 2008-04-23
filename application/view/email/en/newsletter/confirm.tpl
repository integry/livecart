{'STORE_NAME'|config} newsletter e-mail address confirmation
Hello,

To confirm your e-mail address and start receiving our newsletters, please visit this link:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

{include file="email/en/signature.tpl"}

-----------------------------------------------
If you do not want to receive any more newsletter messages from us, please visit the link below to remove yourself from our mailing list:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}