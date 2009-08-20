Ο κωδικός σας πρόσβασης στο {'STORE_NAME'|config}!
Αγαπητέ/ή {$user.fullName},

Εδώ είναι οι πληροφορίες πρόσβασης στο λογαριασμό σας {'STORE_NAME'|config}:

E-mail: <strong><b>{$user.email}</b></strong>
Password: <strong><b>{$user.newPassword}</b></strong>

Μπορείτε να χρησιμοποιήσετε αυτή τη διεύθυνση για να υπογράψετε στο λογαριασμό σας:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}