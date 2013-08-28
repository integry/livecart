Nytt meddelande angående order hos [[ config('STORE_NAME') ]]
En kund har lagt till ett nytt meddelande angående <b class="orderID">#[[order.invoiceNumber]]</b>

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan svara från kontrollpanelens orderhantering:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

[[ partial("email/en/signature.tpl") ]]