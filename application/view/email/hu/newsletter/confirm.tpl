[[ config('STORE_NAME') ]] Hírlevélre való feliratkozás megerősítése
Hello,

Hogy megerősítsd e-mail címedet, és elkezd kapni hírlevelünkett kattints a következő linkre:
[[ fullurl("newsletter/confirm", email=`email`&code=`subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
Ha nem akarod tovább kapni hírlevelünket, kattints az alábbi linkre:
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]