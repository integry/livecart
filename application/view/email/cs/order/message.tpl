Nová zpráva ohledně Vaší objednávky na [[ config('STORE_NAME') ]]
Vážený(á) [[user.fullName]],

Dostal jste novou zprávu ohledně Vaší objednávky.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Odpovědět můžete pomocí tohoto odkazu:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/en/signature.tpl") ]]