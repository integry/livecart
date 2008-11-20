Novo sporočilo glede vašega naročila na {'STORE_NAME'|config}
Spoštovani/a {$user.fullName},

Dodano je bilo novo sporočilo glede vašega naročila.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Na to sporočilo lahko odgovorite s naslednje strani:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}