[[ config('STORE_NAME') ]] Η Παραγγελλία Ακυρώθηκε
Αγαπητέ/ή {$user.fullName},

Η παραγγελλία σας <b class="orderID">#{$order.invoiceNumber}</b>, που δόθηκε [[ config('STORE_NAME') ]], έχει ακυρωθεί.

Εάν έχετε κάποια ερώτηση σχετικά με αυτή την παραγγελλία,μπορείτε να μας στείλετε μήνυμα,ή να επικοινωνήσετε μαζί μας μέσω της παρακάτω σελίδας:
{link controller=user action=viewOrder id=$order.ID url=true}

Είδη ακυρωμένης παραγγελίας:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}