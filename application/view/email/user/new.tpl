Welcome to {$config.STORE_NAME}!
Dear {$user.fullName},

Here are your customer account access information at {$config.STORE_NAME}:

E-mail: {$user.email}
Password: {$user.newPassword}

From your customer account you can instantly see the status of your order, view past orders, download files (for digital item purchases) and change your contact information.

You can use this address to login into your account:
{link controller=user action=login url=true}

{include file="email/signature.tpl"}