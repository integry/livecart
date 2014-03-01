[[ config('STORE_NAME') ]] potvrzení odběru novinek
Dobrý den,

Pro potvrzení přihlášení k odběru novinek našeho obchodu klikněte na tento odkaz:
[[ fullurl("newsletter/confirm", email=`email`&code=`subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
Pokud již nechcete naše novinky odebírat, klikněte na tento odkaz:
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]