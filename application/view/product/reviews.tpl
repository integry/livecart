{loadJs form=true}
{includeJs file="library/lightbox/lightbox.js"}
{includeCss file="library/lightbox/lightbox.css"}

{assign var="metaDescription" value=$product.shortDescription_lang}
{assign var="metaKeywords" value=$product.keywords_lang}
{pageTitle}{$product.name_lang}{/pageTitle}

<div class="reviewIndex productCategory_{$product.Category.ID} product_{$product.ID}">

{include file="product/layout.tpl"}

<div id="content" class="left">

	<fieldset class="container">

		<div class="returnToCategory">
			<a href="{productUrl product=$product}" class="returnToCategory">{$product.name_lang}</a>
		</div>

		<h1>{maketext text="_reviews_for" params=$product.name_lang}</h1>

		<div class="resultStats">
			{include file="product/ratingSummary.tpl"}
			<div class="pagingInfo">
				{maketext text=_showing_reviews params=$offsetStart,$offsetEnd,`$product.reviewCount`}
			</div>
			<div class="clear"></div>
		</div>

		<div class="clear"></div>

		{include file="product/reviewList.tpl"}

		{if $product.reviewCount > $perPage}
			<div class="resultPages">
				{t _pages}: {paginate current=$page count=$product.reviewCount perPage=$perPage url=$url}
			</div>
		{/if}

		{include file="product/ratingForm.tpl"}

	</fieldset>

</div>

{include file="layout/frontend/footer.tpl"}

</div>