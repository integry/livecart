Νέο Μήνυμα Σχετκά με την Παραγγελλία σας στο [[ config('STORE_NAME') ]]
Αγαπητέ/ή [[user.fullName]],

Ένα νέο μήνυμα προστέθηκε σχετικά με την παραγγελλία σας.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Μπορείτε να απαντήσετε σ'αυτό το μήνυμα μέσω της επομένης σελίδας:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/en/signature.tpl") ]]