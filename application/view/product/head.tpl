{include file="block/message.tpl"}

<h1>{$product.name_lang}</h1>

{if $product.listAttributes}
	<div class="specSummary spec">
		{foreach from=$product.listAttributes item="attr" name="attr"}
			{if $attr.values}
				{foreach from=$attr.values item="value" name="values"}
					{$value.value_lang}
					{if !$smarty.foreach.values.last}
					/
					{/if}
				{/foreach}
			{elseif $attr.value}
				{$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
			{elseif $attr.value_lang}
				{$attr.value_lang}
			{/if}

			{if !$smarty.foreach.attr.last}
			/
			{/if}
		{/foreach}
	</div>

	<div style="clear: right;"></div>

{/if}

<div class="clear"></div>

<div id="imageContainer">
	<div id="largeImage" class="{if !$product.DefaultImage.paths.3}missingImage{/if} {if $images|@count > 1}multipleImages{/if}">
		{if $product.DefaultImage.paths.3}

			<a rel="lightbox" href="{$product.DefaultImage.paths.4}" title="{$product.DefaultImage.title_lang|escape}">
				{img src=$product.DefaultImage.paths.3 alt=$product.DefaultImage.title_lang|escape id="mainImage"}
			</a>
		{else}
			{img src="image/missing_large.jpg" alt="" id="mainImage"}
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

<div id="productSummaryContainer">
	<div id="mainInfo">

		{form action="controller=order action=addToCart id=`$product.ID`" handle=$cartForm method="POST"}
		<table id="productPurchaseLinks">

			{if 'DISPLAY_PRICES'|config}
				<tr id="productPrice">
					<td class="param">{t _price}:</td>
					<td class="value price">
							<span class="realPrice">{$product.formattedPrice.$currency}</span>
						{if $product.formattedListPrice.$currency}
							<span class="listPrice">
								{$product.formattedListPrice.$currency}
							</span>
						{/if}
					</td>
				</tr>
				{if $quantityPricing}
					<tr>
						<td colspan="2">
							<table class="quantityPrice">
							{foreach from=$quantityPricing item=quantityPrice key=quant name=quant}
								<tr class="{zebra loop=quant}">
									<td class="quantityAmount">
										{if $quantityPrice.to}
											{$quantityPrice.from} - {$quantityPrice.to}
										{else}
											{maketext text="_x_or_more" params=$quantityPrice.from}
										{/if}
									</td>
									<td class="price quantityPrice">
										{$quantityPrice.formattedPrice}
									</td>
								</tr>
							{/foreach}
							</table>
						</td>
					</tr>
				{/if}
			{/if}

			{if $product.isAvailable && 'ENABLE_CART'|config}
				{if $options}
					<tr id="options">
						<td colspan="2" class="productOptions">
							{include file="product/options.tpl"}
						</td>
					</tr>
				{/if}

				{if $variations.products}
					<tr id="variations">
						<td colspan="2" class="productVariations">
							{include file="product/variations.tpl"}
						</td>
					</tr>
				{/if}

				<tr id="productToCart" class="cartLinks">
					<td class="param">{t _quantity}:</td>
					<td class="value">
						{if !$product.isFractionalUnit}
							{selectfield name="count" options=$quantity}
						{else}
							{textfield name="count" class="text number"}
						{/if}
						<input type="submit" class="submit" value="{tn _add_to_cart}" />
						{hidden name="return" value=$catRoute}
					</td>
				</tr>
			{/if}

			<tr id="productToWishList">
				<td class="param"></td>
				<td class="value cartLinks addToWishList">
					{if 'ENABLE_WISHLISTS'|config}
						<a href="{link controller=order action=addToWishList id=$product.ID query="return=`$catRoute`"}" rel="nofollow">{t _add_to_wishlist}</a>
					{/if}

					{if 'ENABLE_PRODUCT_COMPARE'|config}
						<div class="compare">
							{include file="compare/block/compareLink.tpl"}
						</div>
					{/if}
				</td>
			</tr>
		</table>
		{/form}

		<table id="productMainDetails">
			{if $product.Manufacturer.name}
			<tr>
				<td class="param">{t _manufacturer}:</td>
				<td class="value"><a href="{categoryUrl data=$product.Category addFilter=$manufacturerFilter}">{$product.Manufacturer.name}</a></td>
			</tr>
			{/if}

			{if $product.sku}
			<tr>
				<td class="param">{t _sku}:</td>
				<td class="value">{$product.sku}</td>
			</tr>
			{/if}

			{if $product.stockCount && 'PRODUCT_DISPLAY_STOCK'|config}
			<tr>
				<td class="param">{t _in_stock}:</td>
				<td class="value">{$product.stockCount}</td>
			</tr>
			{/if}

			{if !$product.isDownloadable}
				{if !$product.stockCount && 'PRODUCT_DISPLAY_NO_STOCK'|config}
				<tr>
					<td colspan="2" class="noStock"><span>{t _no_stock}</span></td>
				</tr>
				{/if}

				{if $product.stockCount && 'PRODUCT_DISPLAY_LOW_STOCK'|config}
				<tr>
					<td colspan="2" class="lowStock"><span>{t _low_stock}</span></td>
				</tr>
				{/if}
			{/if}

			{if $product.URL}
			<tr>
				<td colspan="2" class="websiteUrl"><a href="{$product.URL}" target="_blank">{t _product_website}</a></td>
			</tr>
			{/if}

		</table>
	</div>

	{if $product.ratingCount > 0}
		{include file="product/ratingSummary.tpl"}
	{/if}
</div>

<div class="clear"></div>

{literal}
<script type="text/javascript">
{/literal}
	var imageData = $H();
	var imageDescr = $H();
	{foreach from=$images item="image"}
		imageData[{$image.ID}] = {json array=$image.paths};
		imageDescr[{$image.ID}] = {json array=$image.title_lang};
	{/foreach}
	new Product.ImageHandler(imageData, imageDescr);

	var loadingImage = 'image/loading.gif';
	var closeButton = 'image/silk/gif/cross.gif';
</script>