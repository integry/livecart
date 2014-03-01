{form handle=form action="controller=backend.review action=update id=`review.ID`" onsubmit="Backend.Review.Editor.prototype.getInstance(`review.ID`, false).submitForm(); return false;" method="post" role="product.update"}

	{foreach ratingTypes as type}
		{input name="rating_`type.ID`"}
			{label}{type.name()|@or:_rating}:{/label}
			{selectfield options=ratingOptions}
		{/input}
	{% endfor %}

	<p class="required">
		[[ textfld('nickname', '_nickname') ]]

		[[ textfld('title', '_title') ]]

		[[ textareafld('text', '_text') ]]
	</p>

	[[ partial('backend/eav/fields.tpl', ['item': review]) ]]

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _save}">
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>

{/form}