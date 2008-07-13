<div id="ratingSummary">
	<fieldset class="container">
		<div class="overallRating">{t _overall_rating}: <img src="image/rating/{ {$product.rating*2|@round}/2}.gif" /></div>
		{if $ratings}
			<ul class="ratingBreakdown">
			{foreach $ratings as $rating}
				<li>{$rating.RatingType.name_lang}: <img src="image/rating/category_{ {$rating.rating*2|@round}/2}.gif" /></li>
			{/foreach}
			</ul>
		{/if}
	</fieldset>
</div>