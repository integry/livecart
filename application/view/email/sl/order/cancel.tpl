{'STORE_NAME'|config} Naročilo preklicano
Spoštovani/a {$user.fullName},

Vaše naročilo #{$order.ID}, naročeno na {'STORE_NAME'|config}, je bilo preklicano.

Če imate karkšna koli vprašanja glede naročila, nam lahko pošljete email ali nas kontaktirate preko naslednje strani:
{link controller=user action=viewOrder id=$order.ID url=true}

Izdelki s preklicanega naročila:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}