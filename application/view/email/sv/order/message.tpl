Nytt meddelande om din order hos [[ config('STORE_NAME') ]]
Kära [[user.fullName]],

Ett nytt meddelande har lagts till rörande din order.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan svara på meddelandet via följande länk:
{link controller=user action=viewOrder id=$order.ID url=true}

[[ partial("email/en/signature.tpl") ]]