{form action="controller=product action=rate id=`$product.ID`" handle=$ratingForm method="POST" onsubmit="new Product.Rating(this); return false;" class="form-horizontal"}
<table class="productDetailsTable">
	<tr class="first heading">
		<td class="param"></td>
		{section start=0 loop='RATING_SCALE'|config name=rate}
			{assign var=index value=$smarty.section.rate.index+1}
			<td class="{% if $smarty.section.rate.last %}value{% endif %}">[[index]]</td>
		{/section}
		<td class="ratingPreview"></td>
	</tr>
{foreach from=$ratingTypes item=type name=types}
	<tr>
		<td class="param ratingCategoryName">
			{$type.name_lang|@or:{t _default_rating_category}}
		</td>
		{section start=0 loop='RATING_SCALE'|config name=rate}
			{assign var=index value=$smarty.section.rate.index+1}
			<td class="{% if $smarty.section.rate.last %}value{% endif %}">
				{radio name="rating_`$type.ID`" value=$index onchange="Product.Rating.prototype.updatePreview(event);"}
				{% if $smarty.section.rate.last %}
					<div class="text-danger hidden">{error for="rating_`$type.ID`"}{/error}</div>
				{% endif %}
			</td>
		{/section}
			<td class="ratingPreview"><img src="" style="display: none;" alt="Rating" /></td>
	</tr>
{/foreach}
</table>

<input type="hidden" name="rating" />
<div class="text-danger hidden">{error for="rating"}{/error}</div>

{% if !'ENABLE_REVIEWS'|config || !$ratingForm|@isRequired:'nickname' %}
	<p>
		<input class="submit" type="submit" value="{t _submit_rating}" /> <span class="progressIndicator" style="display: none;"></span>
	</p>
	<div class="clear"></div>
{% endif %}

{% if 'ENABLE_REVIEWS'|config %}
	<div class="reviewForm">
		[[ textfld('nickname', '_nickname') ]]

		[[ textfld('title', '_summary') ]]

		[[ textareafld('text', '_review_text') ]]
	</div>

	[[ partial('block/submit.tpl', ['caption': "_submit_review"]) ]]
{% endif %}

{/form}
