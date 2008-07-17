{loadJs form=true}
{includeJs file="library/lightbox/lightbox.js"}
{includeCss file="library/lightbox/lightbox.css"}

{assign var="metaDescription" value=$product.shortDescription_lang}
{assign var="metaKeywords" value=$product.keywords_lang}
{pageTitle}{$product.name_lang}{/pageTitle}

<div class="productIndex productCategory_{$product.Category.ID} product_{$product.ID}">

{include file="product/layout.tpl"}

<div id="content" class="left">
	<fieldset class="container">

		<div class="returnToCategory">
			<a href="{link route=$catRoute}" class="returnToCategory">{$product.Category.name_lang}</a>
		</div>

		{include file="product/head.tpl"}
		{include file="product/details.tpl"}
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