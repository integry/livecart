{loadJs}
{includeJs file="library/lightbox/lightbox.js"}
{includeCss file="library/lightbox/lightbox.css"}

{assign var="metaDescription" value=$product.shortDescription_lang}
{assign var="metaKeywords" value=$product.keywords_lang}
{pageTitle}{$product.name_lang}{/pageTitle}

<div class="productIndex productCategory_{$product.Category.ID} product_{$product.ID}">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left">

	<div class="returnToCategory">
		{assign var="lastBreadcrumb" value=$breadCrumb|@end}
		{assign var="lastBreadcrumb" value=$breadCrumb|@prev}
		<a href="{$lastBreadcrumb.url}" class="returnToCategory">{$product.Category.name_lang}</a>
	</div>

	<h1>{$product.name_lang}</h1>

	{if $product.listAttributes}
		<div class="specSummary">
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
					{img src=$product.DefaultImage.paths.3 alt=$product.DefaultImage.name_lang|escape id="mainImage"}
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

	<div id="mainInfo">

		{form action="controller=order action=addToCart id=`$product.ID`" handle=$cartForm method="POST"}
		<table id="productPurchaseLinks">
			<tr id="productPrice">
				<td class="param">{t _price}:</td>
				<td class="value price">
					{$product.formattedPrice.$currency}
					{if $product.formattedListPrice.$currency}
						<span class="listPrice">
							{$product.formattedListPrice.$currency}
						</span>
					{/if}
				</td>
			</tr>

			{if 'ENABLE_CART'|config}
			{if $options}
				<tr>
					<td colspan="2" class="productOptions">
						{include file="product/options.tpl"}
					</td>
				</tr>
			{/if}

			<tr id="productToCart" class="cartLinks">
				<td class="param">{t _quantity}:</td>
				<td class="value">
					{selectfield name="count" options=$quantity}
					<input type="submit" class="submit" value="{tn _add_to_cart}" />
					{hidden name="return" value=$catRoute}
				</td>
			</tr>
			{/if}

			<tr id="productToWishList">
				<td class="param"></td>
				<td class="value cartLinks addToWishList">
					{if 'ENABLE_WISHLISTS'|config}
						<a href="{link controller=order action=addToWishList id=$product.ID query="return=`$catRoute`"}">{t _add_to_wishlist}</a>
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

   	<div class="clear"></div>

	{if $product.longDescription_lang || $product.shortDescription_lang}
	<h2>{t _description}</h2>
	<div id="productDescription">
		{if $product.longDescription_lang}
			{$product.longDescription_lang}
		{else}
			{$product.shortDescription_lang}
		{/if}
	</div>
	{/if}

	{if $product.attributes}
	<h2>{t _spec}</h2>
	<div id="productSpecification">
		<table>
			{foreach from=$product.attributes item="attr" name="attributes"}

				{if $prevAttr.SpecField.SpecFieldGroup.ID != $attr.SpecField.SpecFieldGroup.ID}
					<tr class="specificationGroup{if $smarty.foreach.attributes.first} first{/if}">
						<td class="param">{$attr.SpecField.SpecFieldGroup.name_lang}</td>
						<td class="value"></td>
					</tr>
				{/if}
				<tr class="{zebra loop="attributes"} {if $smarty.foreach.attributes.first && !$attr.SpecField.SpecFieldGroup.ID}first{/if}{if $smarty.foreach.attributes.last} last{/if}">
					<td class="param">{$attr.SpecField.name_lang}</td>
					<td class="value">
						{if $attr.values}
							<ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
								{foreach from=$attr.values item="value"}
									<li> {$value.value_lang}</li>
								{/foreach}
							</ul>
						{elseif $attr.value_lang}
							{$attr.value_lang}
						{elseif $attr.value}
							{$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
						{/if}
					</td>
				</tr>
				{assign var="prevAttr" value=$attr}

			{/foreach}
		</table>
	</div>
	{/if}

	{if $related}
	<h2>{t _recommended}</h2>
	<div id="relatedProducts">

		{foreach from=$related item=group}

		   {if $group.0.ProductRelationshipGroup.name_lang}
			   <h3>{$group.0.ProductRelationshipGroup.name_lang}</h3>
		   {/if}

		   {include file="category/productList.tpl" products=$group}

		{/foreach}

	</div>
	{/if}

	{if $together}
	<h2>{t _purchased_together}</h2>
	<div id="purchasedTogether">

		{include file="category/productList.tpl" products=$together}

	</div>
	{/if}

	{if $reviews}
	<h2>{t _reviews}</h2>
	{/if}

</div>

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

{include file="layout/frontend/footer.tpl"}

</div>