{% if $fieldList|@is_null %}
	{assign var=fieldList value=$specFieldList_prefix[$eavPrefix]}
{% endif %}

{include file="backend/eav/fields.tpl" field=EavField specFieldList=$fieldList disableNewOptionValues=true disableAutocomplete=true prefix=$eavPrefix}