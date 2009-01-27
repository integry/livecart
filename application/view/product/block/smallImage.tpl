<div class="image">
	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">
	{if $product.DefaultImage.ID}
		{img src=$product.DefaultImage.paths.2 alt=$product.name_lang|escape}
	{else}
		{img src=image/missing_small.jpg alt=$product.name_lang|escape}
	{/if}
	</a>
</div>