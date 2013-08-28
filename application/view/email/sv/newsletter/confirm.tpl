[[ config('STORE_NAME') ]] bekräfta e-mail adress för nyhetsbrev
Hej,

För att bekräfta din e-mail adress och börja ta emot våra nyhetsbrev, v.g. besök länken:
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

{include file="email/en/signature.tpl"}

-----------------------------------------------
Om du inte längre vill ta emot våra nyhetsbrev kan du avbeställa prenumerationen genom länken nedan:
{link controller=newsletter ac