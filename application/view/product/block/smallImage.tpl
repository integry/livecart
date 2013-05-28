<div class="image">
	{block LIST-IMAGE}
	{block QUICK-SHOP product=$product}

	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle category=$category}">
	{if $product.DefaultImage.ID}
		{img src=$product.DefaultImage.urls.2 alt=$product.name_lang|escape}
	{else}
		{img src='MISSING_IMG_SMALL'|config alt=$product.name_lang|escape}
	{/if}
	</a>
</div>