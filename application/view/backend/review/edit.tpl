{form handle=$form action="controller=backend.review action=update id=`$review.ID`" onsubmit="Backend.Review.Editor.prototype.getInstance(`$review.ID`, false).submitForm(); return false;" method="post" role="product.update"}

	{foreach $ratingTypes as $type}
		<p class="required">
			{assign var=title value=`{$type.name_lang|@or:_rating}`}
			{err for="rating_`$type.ID`"}
				{label $title}
				{selectfield options=$ratingOptions}
			{/err}
		</p>
	{/foreach}

	<p class="required">
		{err for="nickname"}
			{label _nickname}
			{textfield}
		{/err}

		{err for="title"}
			{label _title}
			{textfield}
		{/err}

		{err for="text"}
			{label _text}
			{textarea}
		{/err}
	</p>

	{include file="backend/eav/fields.tpl" item=$review}

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _save}">
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>

{/form}