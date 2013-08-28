[[ config('STORE_NAME') ]] Order Cancelled
Kære [[user.fullName]],

Din ordre <b class="orderID">#[[order.invoiceNumber]]</b>, bestilt hos [[ config('STORE_NAME') ]], er blevet annulleret.

Hvis du har spørgsmål vedrørende denne ordre, er du velkommen til at kontakte os pr. E-mail, eller kontakte os på følgende side:
{link controller=user action=viewOrder id=$order.ID url=true}

Enheder på den annullerede ordre:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}