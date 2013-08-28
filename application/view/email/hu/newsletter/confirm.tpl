[[ config('STORE_NAME') ]] Hírlevélre való feliratkozás megerősítése
Hello,

Hogy megerősítsd e-mail címedet, és elkezd kapni hírlevelünkett kattints a következő linkre:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

{include file="email/en/signature.tpl"}

-----------------------------------------------
Ha nem akarod tovább kapni hírlevelünket, kattints az alábbi linkre:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}