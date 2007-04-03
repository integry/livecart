<span>
    {if $product.DefaultImage}
        <img src="{$product.DefaultImage.paths[1]}" alt="{$product.DefaultImage.title}" title="{$product.DefaultImage[1].title}" />
    {/if}
    <span class="productRelationship_title">{$product.name_lang}</span>
</span>