{if $ratings && $ratings.0.RatingType.ID}
	<ul class="ratingBreakdown">
	{foreach $ratings as $rating}
		<li>[[rating.RatingType.name_lang]]: <img src="image/rating/category_{ {$rating.rating*2|@round}/2}.gif" /></li>
	{/foreach}
	</ul>
{/if}