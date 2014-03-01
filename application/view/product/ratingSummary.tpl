{% if product.ratingCount > 0 %}
	<div id="ratingSummary">
		<div class="overallRating">
			<span>{t _overall_rating}:</span> [[ partial('product/ratingImage.tpl', ['rating': product.rating]) ]]
			{% if product.reviewCount > 0 %}
				<a href="{self}#reviews">({maketext text="_review_count" params=product.reviewCount})</a>
			{% endif %}
		</div>

		[[ partial("product/ratingBreakdown.tpl") ]]
	</div>
{% endif %}