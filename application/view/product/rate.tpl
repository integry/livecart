{form action="controller=product action=rate id=`$product.ID`" handle=$ratingForm method="POST" onsubmit="new Product.Rating(this); return false;"}
<table class="productTable">
	<tr class="first heading">
		<td class="param"></td>
		{section start=0 loop='RATING_SCALE'|config name=rate}
			{assign var=index value=$smarty.section.rate.index+1}
			<td class="{if $smarty.section.rate.last}value{/if}">{$index}</td>
		{/section}
		<td class="ratingPreview"></td>
	</tr>
{foreach from=$ratingTypes item=type name=types}
	<tr class="{zebra loop="types"}{if $smarty.foreach.types.last} last{/if}">
		<td class="param ratingCategoryName">
			{$type.name_lang|@or:{t _default_rating_category}}
		</td>
		{section start=0 loop='RATING_SCALE'|config name=rate}
			{assign var=index value=$smarty.section.rate.index+1}
			<td class="{if $smarty.section.rate.last}value{/if}">
				{radio name="rating_`$type.ID`" value=$index onchange="Product.Rating.prototype.updatePreview(event);"}
				{if $smarty.section.rate.last}
					<div class="errorText hidden">{error for="rating_`$type.ID`"}{/error}</div>
				{/if}
			</td>
		{/section}
			<td class="ratingPreview"><img src="" style="display: none;" alt="Rating" /></td>
	</tr>
{/foreach}
</table>

<input type="hidden" name="rating" />
<div class="errorText hidden">{error for="rating"}{/error}</div>

{if !'ENABLE_REVIEWS'|config || !$ratingForm|@isRequired:'nickname'}
	<p>
		<input class="submit" type="submit" value="{tn _submit_rating}" /> <span class="progressIndicator" style="display: none;"></span>
	</p>
	<div class="clear"></div>
{/if}

{if 'ENABLE_REVIEWS'|config}
	<div class="reviewForm">
		<p{if $ratingForm|@isRequired:'nickname'} class="required"{/if}>
			{err for="nickname"}
				<label class="wide">{t _nickname}:</label>
				{textfield class="text wide"}
			{/err}
		</p>
		<p{if $ratingForm|@isRequired:'title'} class="required"{/if}>
			{err for="title"}
				<label class="wide">{t _summary}:</label>
				{textfield class="text wide"}
			{/err}
		</p>
		<p{if $ratingForm|@isRequired:'text'} class="required"{/if}>
			{err for="text"}
				<label class="wide">{t _review_text}:</label>
				{textarea}
			{/err}
		</p>
	</div>
	<p>
		<input class="submit" type="submit" value="{tn _submit_review}" /> <span class="progressIndicator" style="display: none;"></span>
	</p>
{/if}

{/form}