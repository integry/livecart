Your password at [[ config('STORE_NAME') ]]!
Dear [[usr.getFullName()]],

Here are your customer account access information at [[ config('STORE_NAME') ]]:

E-mail: <strong><b>[[usr.email]]</b></strong>
Password: <strong><b>[[usr.getPassword()]]</b></strong>

You can use this address to login into your account:
[[ fullurl("user/login") ]]

[[ partial("email/en/signature.tpl", ['html': html]) ]]