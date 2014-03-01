Nový vzkaz k objednávce na [[ config('STORE_NAME') ]]
Zákazník poslal vzkaz k objednávce č.: [[order.invoiceNumber]]

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Můžete mu odpovědět zde:
{backendOrderUrl order=order url=true}#tabOrderCommunication__

[[ partial("email/en/signature.tpl") ]]