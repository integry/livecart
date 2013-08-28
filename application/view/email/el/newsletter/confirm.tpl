[[ config('STORE_NAME') ]]επιβεβαίωση ηλεκτρονικής διεύθυνσης Νewsletter
Γειά σας,

Για να επιβεβαιώσουμε την ηλεκτρονική σας διεύθυνση και αρχίσετε να λαμβάνετε το ενημερωτικό δελτίο μας,παρακαλούμε επισκεφθείτε αυτό το σύνδεσμο:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

{include file="email/en/signature.tpl"}

-----------------------------------------------
Εάν δεν επιθυμείτε να λαμβάνετε το ενημερωτικό δελτίο μας,παρακαλούμε επισκεφθείτε τον παρακάτω σύνδεσμο:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}