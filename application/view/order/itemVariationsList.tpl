{foreach from=$item.Product.variations item=variation name=variations}
<span class="variationName">[[variation.name_lang]]</span>{if !$smarty.foreach.variations.last} <span class="variationSeparator">/</span> {/if}
{/foreach}
