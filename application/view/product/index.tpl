{loadJs form=true}
{includeJs file="frontend/Product.js"}

{assign var="metaDescription" value=$product.shortDescription_lang|@strip_tags}
{assign var="metaKeywords" value=$product.keywords}
{canonical}{productUrl product=$product}{/canonical}
{pageTitle}{$product.pageTitle_lang|default:$product.name_lang}{/pageTitle}

<div class="productIndex productCategory_{$product.Category.ID} product_{$product.ID}">

{include file="product/layout.tpl"}
{include file="block/content-start.tpl" hideTitle=true}

{include file="product/head.tpl"}

<div class="row" id="productNavigation">
	<div class="col col-lg-12">
		{block PRODUCT-NAVIGATION}  {* product/block/images.tpl *}
	</div>
</div>

{if 'PRODUCT_TABS'|config}
	<ul class="nav nav-tabs" id="productTabs"></ul>
{/if}

<div id="productContent" class="productContent">
	{if $product.type == 2}
		{include file="product/bundle.tpl"}
	{/if}

	{include file="product/files.tpl"}

	{include file="product/details.tpl"}

	{if 'PRODUCT_INQUIRY_FORM'|config}
		{include file="product/contactForm.tpl"}
	{/if}

	{include file="product/ratingForm.tpl"}
	{include file="product/sendToFriendForm.tpl"}

	{if $reviews}
		<div id="reviewSection" class="productSection reviewSection">
			<h2>{t _reviews}<small>{t _tab_reviews}</small></h2>
			{include file="product/reviewList.tpl"}

			{if $product.reviewCount  > $reviews|@count}
				<a href="{link product/reviews id=$product.ID}" class="readAllReviews">{maketext text="_read_all_reviews" params=$product.reviewCount}</a>
			{/if}
		</div>
	{/if}
</div>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}

</div>
