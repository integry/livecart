Complete your registration at [[ config('STORE_NAME') ]]!
Dear [[user.fullName]],

Thank you for registering at [[ config('STORE_NAME') ]]! To confirm your e-mail address complete your registration, please visit the following URL:
[[ fullurl("user/confirm", email=`user.email`&code=`code`) ]]

[[ partial("email/en/signature.tpl") ]]