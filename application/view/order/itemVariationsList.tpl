{foreach from=$item.Product.variations item=variation name=variations}
<span class="variationName">[[variation.name()]]</span>{% if !$smarty.foreach.variations.last %} <span class="variationSeparator">/</span> {% endif %}
{/foreach}
