[[ config('STORE_NAME') ]] Order anullerad
Kära [[user.fullName]],

Din order <b class="orderID">#[[order.invoiceNumber]]</b>, hos [[ config('STORE_NAME') ]], har annullerats.

Om du har några frågor om ordern kan du sända oss ett e-mail eller kontakta oss via följande länk:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Den annullerade ordern innehöll följande varor:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]