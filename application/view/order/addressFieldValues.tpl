{* NB: DO NOT INDENT or plaintext email formating will fail *}
{function name="attributeValue"}
{assign var="field" value=$field|default:"SpecField"}
{% if $attr.values %}
<ul class="attributeList{% if $attr.values|@count == 1 %} singleValue{% endif %}">{foreach from=$attr.values item="value"}
<li> [[value.value()]]</li>{/foreach}</ul>
{% elseif $attr.value() %}[[attr.value()]]
{% elseif $attr.value %}{$attr.$field.valuePrefix()}[[attr.value]]{$attr.$field.valueSuffix()}
{% endif %}{/function}
{foreach $address.attributes as $attr}
{% if $attr.EavField && ($attr.values || $attr.value || $attr.value()) %}
{% if !empty(showLabels) %}<label class="attrName">[[attr.EavField.name()]]:</label>  <label class="attrValue">{attributeValue attr=$attr field="EavField"}</label>
{% else %}<p class="attrValue">{attributeValue attr=$attr field="EavField"}</p>{% endif %}
{% endif %}
{/foreach}