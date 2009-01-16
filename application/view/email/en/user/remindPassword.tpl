Your password at {'STORE_NAME'|config}!
Dear {$user.fullName},

Here are your customer account access information at {'STORE_NAME'|config}:

E-mail: <strong><b>{$user.email}</b></strong>
Password: <strong><b>{$user.newPassword}</b></strong>

You can use this address to login into your account:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}