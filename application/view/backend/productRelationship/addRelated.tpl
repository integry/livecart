<span>
    {if $product.DefaultImage}
        <img src="{$product.DefaultImage.paths[1]}" alt="{$product.DefaultImage.title}" title="{$product.DefaultImage[1].title}" />
    {/if}
    <span style="font-size: 1.5em; font-weight: bold;">{$product.name_lang}</span>
</span>