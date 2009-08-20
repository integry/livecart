{'STORE_NAME'|config} Επιβεβαίωση Παραγγελλίας
Αγαπητέ/ή {$user.fullName},

Ευχαρισθούμε για την παραγγελλία σας που δόθηκε στο {'STORE_NAME'|config}. Εάν χρειασθεί να επικοινωνήσετε μαζί μας σχετικά με αυτή την παραγγελλία,παρακαλούμε αναφέρατε τον κωδικό(ID) αυτής <b class="orderID">#{$order.invoiceNumber}</b>.

Μπορείτε να παρακολουθήσετε την πορεία της παραγγελλία σας σ'αυτή τη σελίδα:
{link controller=user action=viewOrder id=$order.ID url=true}

Εάν έχετε οποιαδήποτε ερώτηση σχετικά με αυτήτην παραγγελλία,μπορείτε να στείλετε ένα μήνυμα μέσω της παραπάνω σελίδας επίσης.

Σας υπενθυμίζουμε ότι έχουν παραγγελθεί τα ακόλουθα είδη:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}