{% set fields = config('USER_FIELDS') %}
{% if empty(prefix) %}{% set prefix = '' %}{% endif %}

[[ partial("user/block/nameFields.tpl", ['fields': fields, 'prefix': prefix]) ]]
[[ partial("user/block/phoneField.tpl", ['fields': fields, 'prefix': prefix]) ]]
[[ partial("user/block/addressFields.tpl", ['fields': fields, 'prefix': prefix]) ]]

{# [[ partial('block/eav/fields.tpl', ['item': address, 'eavPrefix': prefix]) ]] #}
