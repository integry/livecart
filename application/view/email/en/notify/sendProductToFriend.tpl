You may be interested in produt at [[ config('STORE_NAME') ]]
Hello!
Your friend [[friendName]] wants you to take a look at this product
[[product.name]] ({productUrl product=$product full=true})

{% if $notes %}
He also added:
[[notes]]
{% endif %}

[[ partial("email/en/signature.tpl") ]]