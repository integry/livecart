{% if !$html %}
------------------------
[[ config('STORE_NAME') ]]
{link url=true}
{% endif %}{*html*}
{% if $html %}
<hr /><a href="{link url=true}">[[ config('STORE_NAME') ]]</a>
{% endif %}{*html*}