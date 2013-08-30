[[subject]]
{% if !empty(html) %}[[htmlMessage]]
{% else %}[[text]]{% endif %}

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
If you do not want to receive any more newsletter messages from us, please visit the link below to remove yourself from our mailing list:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}