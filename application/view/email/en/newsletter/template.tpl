[[subject]]
{% if !empty(html) %}[[htmlMessage]]
{% else %}[[text]]{% endif %}

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
If you do not want to receive any more newsletter messages from us, please visit the link below to remove yourself from our mailing list:
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]