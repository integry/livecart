{* Function to generate address output (address template) *}
{% if $address %}
[[address.fullName]]
{% if $address.companyName %}
[[address.companyName]]
{% endif %}
{% if $address.address1 %}
[[address.address1]]
{% endif %}
{% if $address.address2 %}
[[address.address2]]
{% endif %}
{% if $address.city %}
[[address.city]]
{% endif %}
{% if $address.stateName %}[[address.stateName]]{% if $address.postalCode %}, {% endif %}{% endif %}[[address.postalCode]]
{% if $address.countryName %}
[[address.countryName]]
{% endif %}{include file="order/addressFieldValues.tpl" showLabels=false}
{% endif %}