New Message Regarding Your Order at [[ config('STORE_NAME') ]]
Dear {$user.fullName},

A new message has been added regarding your order.

--------------------------------------------------
{$message.text}
--------------------------------------------------

You can respond to this message from the following page:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}