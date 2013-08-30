[[ config('STORE_NAME') ]] Potvrzení objednávky
Vážený(á) [[user.fullName]],

Děkujeme za objednávku v našem obchodě [[ config('STORE_NAME') ]].
Pokud nás ohledně této objednávky chcete kontaktovat, uvádějte její číslo: [[order.invoiceNumber]].

Stav objednávky můžete sledovat zde:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Kontaktovat nás můžete použitím odkazu nahoře.

We remind that the following items have been ordered:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]