Another Attempt: Your password at {$config.STORE_NAME}!
Dear {$user.fullName},

Here are your customer account access information at {$config.STORE_NAME}:

E-mail: {$user.email}
Password: {$user.newPassword}

You can use this address to login into your account:
{link controller=user action=login url=true}

{include file="email/signature.tpl"}