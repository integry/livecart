Your password at {'STORE_NAME'|config}!
Dear {$user.fullName},

Here are your customer account access information at {$config.STORE_NAME}:

E-mail: {$user.email}
Password: {$user.newPassword}

You can use this address to login into your account:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}