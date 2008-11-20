Nový vzkaz k objednávce na {'STORE_NAME'|config}
Zákazník poslal vzkaz k objednávce č.: {$order.ID}

--------------------------------------------------
{$message.text}
--------------------------------------------------

Můžete mu odpovědět zde:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}