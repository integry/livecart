{$subject}
{$text}

{include file="email/en/signature.tpl"}

-----------------------------------------------
Ha nem akarod tovább kapni levelünket, kattints az alábbi linkre:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}