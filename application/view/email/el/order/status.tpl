[[ config('STORE_NAME') ]] Ενημέρωση Καταλόγου Παραγγελλίας
Αγαπητέ/ή [[user.fullName]],

{% if order.shipments|@count == 1 %}
Ο Κατάλογος ενημερώθηκε για την παραγγελλία σας <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
Ο Κατάλογος έχει ενημερωθεί για μία ή περισσότερες αποστολές από την παραγγελλία σας <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Εάν έχετε οποιαδήποτε ερώτηση σχετικά με την παραγγελλία σας,μπορείτε να μας στείλετε ένα μήνυμα ή να επικοινωνήσετε μαζί μας μέσω της επομένης σελίδας:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=order.shipments item=shipment}
Νέος Κατάλογος: {% if shipment.status == 2 %}awaiting shipment{% elseif shipment.status == 3 %}shipped{% elseif shipment.status == 4 %}returned{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{% endfor %}

[[ partial("email/en/signature.tpl") ]]