<div id="imageContainer">
	<div id="largeImage" class="{if $images|@count == 0}missingImage{/if} {if $images|@count > 1}multipleImages{/if}">
		{if $product.DefaultImage.paths.3}

			<a rel="lightbox" href="{$product.DefaultImage.paths.4}" title="{$product.DefaultImage.title_lang|escape}">
				{img src=$product.DefaultImage.paths.3 alt=$product.DefaultImage.title_lang|escape id="mainImage"}
			</a>
		{else}
			{img src='MISSING_IMG_LARGE'|config alt="" id="mainImage"}
		{/if}
	</div>
	{if $images|@count > 1}
		<div id="moreImages">
			{foreach from=$images item="image"}
				{img src=$image.paths.1 id="img_`$image.ID`" alt=$image.name_lang|escape onclick="return false;"}
			{/foreach}
		</div>
	{/if}
</div>

{literal}
<script type="text/javascript">
{/literal}
	var imageData = $H();
	var imageDescr = $H();
	var imageProducts = $H();
	{foreach from=$images item="image"}
		imageData[{$image.ID}] = {json array=$image.paths};
		imageDescr[{$image.ID}] = {json array=$image.title_lang};
		imageProducts[{$image.ID}] = {json array=$image.productID};
	{/foreach}
	new Product.ImageHandler(imageData, imageDescr, imageProducts);

	var loadingImage = 'image/loading.gif';
	var closeButton = 'image/silk/gif/cross.gif';
</script>