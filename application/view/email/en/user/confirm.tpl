Complete your registration at {'STORE_NAME'|config}!
Dear {$user.fullName},

Thank you for registering at {'STORE_NAME'|config}! To confirm your e-mail address complete your registration, please visit the following URL:
{link controller=user action=confirm query="email=`$user.email`&code=`$code`" url=true}

{include file="email/en/signature.tpl"}