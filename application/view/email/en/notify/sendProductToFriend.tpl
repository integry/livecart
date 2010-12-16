You may be interested in produt at {'STORE_NAME'|config}
Hello!
Your friend {$friendName} wants you to take a look at this product
{$product.name} ({productUrl product=$product full=true})

{if $notes}
He also added:
{$notes}
{/if}

{include file="email/en/signature.tpl"}