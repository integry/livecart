Νέο Μήνυμα Παραγγελίας στο [[ config('STORE_NAME') ]]
Ένας πελάτης πρόσθεσε ένα νέο μήνυμα σχετικά με την παραγγελλία <b class="orderID">#{$order.invoiceNumber}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Μπορείτε να προσθέσετε μια απάντηση από το panel διαχείρισης παραγγελλιών:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}