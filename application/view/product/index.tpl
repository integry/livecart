{loadJs form=true}

{assign var="metaDescription" value=$product.shortDescription_lang|@strip_tags}
{assign var="metaKeywords" value=$product.keywords}

{pageTitle}{$product.pageTitle_lang|default:$product.name_lang}{/pageTitle}

<div class="productIndex productCategory_{$product.Category.ID} product_{$product.ID}">

{include file="product/layout.tpl"}

<div id="content" class="left">
	<fieldset class="container">

		<div class="returnToCategory">
			<a href="{link route=$catRoute}">{$product.Category.name_lang}</a>
		</div>

		{include file="product/head.tpl"}

		{if $product.type == 2}
			{include file="product/bundle.tpl"}
		{/if}

		{include file="product/files.tpl"}

		{include file="product/details.tpl"}

		{if 'PRODUCT_INQUIRY_FORM'|config}
			{include file="product/contactForm.tpl"}
		{/if}

		{include file="product/ratingForm.tpl"}

		{if $reviews}
			<h2>{t _reviews}</h2>
			{include file="product/reviewList.tpl"}

			{if $product.reviewCount  > $reviews|@count}
				<a href="{link product/reviews id=$product.ID}" class="readAllReviews">{maketext text="_read_all_reviews" params=$product.reviewCount}</a>
			{/if}
		{/if}

	</fieldset>
</div>

{include file="layout/frontend/footer.tpl"}

</div>