{% if empty(html) %}
------------------------
[[ config('STORE_NAME') ]]

[[ fullurl("/") ]]
{% endif %}{*html*}
{% if !empty(html) %}
<hr /><a href="[[ fullurl("/") ]]">[[ config('STORE_NAME') ]]</a>
{% endif %}{*html*}