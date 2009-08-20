{$subject}
{$text}

{include file="email/en/signature.tpl"}

-----------------------------------------------
Εάν δεν επιθυμείτε να λαμβάνετε ενημερωτικά μηνύματα,παρακαλούμε επισκεφθείτε τον παρακάτω σύνδεσμο για να διαγραφείτε από τον ταχυδρομικό μας κατάλογο:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}