{% if 'SHOW_SKU_EMAIL'|config %}{% set SHOW_SKU = true %}{% endif %}
{% if empty(html) %}
{% if !empty(SHOW_SKU) %}----------{% endif %}------------------------------------------------------------
{% if !empty(SHOW_SKU) %}[[ @str_pad_iconv:10({t _sku}) ]]{% endif %}[[ @str_pad_iconv:25({t _product}) ]][[ @str_pad_iconv:11({t _price}) ]][[ @str_pad_iconv:9({t _qty}) ]]{t _subtotal}
{% if !empty(SHOW_SKU) %}----------{% endif %}------------------------------------------------------------
{% endif %}{*html*}
{% if !empty(html) %}
{% if empty(noTable) %}<table>{% endif %}
<thead>
<tr>
	{% if !empty(SHOW_SKU) %}<th>{t _sku}</th>{% endif %}<th>{t _product}</th><th>{t _price}</th><th>{t _qty}</th><th>{t _subtotal}</th>
</tr>
</thead>
{% endif %}{*html*}