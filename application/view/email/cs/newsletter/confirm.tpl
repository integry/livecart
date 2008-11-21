{'STORE_NAME'|config} potvrzení odběru novinek
Dobrý den,

Pro potvrzení přihlášení k odběru novinek našeho obchodu klikněte na tento odkaz:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

{include file="email/en/signature.tpl"}

-----------------------------------------------
Pokud již nechcete naše novinky odebírat, klikněte na tento odkaz:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}