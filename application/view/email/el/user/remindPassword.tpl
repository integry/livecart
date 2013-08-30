Ο κωδικός σας πρόσβασης στο [[ config('STORE_NAME') ]]!
Αγαπητέ/ή [[user.fullName]],

Εδώ είναι οι πληροφορίες πρόσβασης στο λογαριασμό σας [[ config('STORE_NAME') ]]:

E-mail: <strong><b>[[user.email]]</b></strong>
Password: <strong><b>[[user.newPassword]]</b></strong>

Μπορείτε να χρησιμοποιήσετε αυτή τη διεύθυνση για να υπογράψετε στο λογαριασμό σας:
[[ fullurl("user/login") ]]

[[ partial("email/en/signature.tpl") ]]