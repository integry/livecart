<div id="ratingSummary">
	<fieldset class="container">
		<div class="overallRating">
			<span>{t _overall_rating}:</span> {include file="product/ratingImage.tpl" rating=$product.rating}
			{if $product.reviewCount > 0}
				<a href="{self}#reviews">({maketext text="_review_count" params=$product.reviewCount})</a>
			{/if}
		</div>

		{include file="product/ratingBreakdown.tpl"}

	</fieldset>
</div>