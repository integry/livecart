Complete your registration at [[ config('STORE_NAME') ]]!
Dear [[user.fullName]],

Thank you for registering at [[ config('STORE_NAME') ]]! To confirm your e-mail address complete your registration, please visit the following URL:
{link controller=user action=confirm query="email=`$user.email`&code=`$code`" url=true}

{include file="email/en/signature.tpl"}