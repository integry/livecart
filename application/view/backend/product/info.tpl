<p style="line-height: 3em;">
	{t _date_created}: [[product.formatted_dateCreated.date_long]] [[product.formatted_dateCreated.time_long]]
</p>

<fieldset>
	<legend>{t _purchase_stats}</legend>

	<form>
		{foreach from=$purchaseStats key=key item=count}
			<p>
				<label>{translate text=$key}</label>
				<label>[[count]]</label>
			</p>
		{foreachelse}
			<div class="noRecords"><div>{t _no_purchases}</div></div>
		{/foreach}
	</form>

</fieldset>

{% if !empty(together) %}
<fieldset class="purchasedTogether">
	<legend>{t _together_with}</legend>

	<ul class="activeList">
	{foreach from=$together item=product}
		<li class="activeList_odd" style="line-height: 3em;">
		<span>
			<fieldset class="container">
				<div class="productRelationship_image">
					{% if $product.DefaultImage %}
						{img src=$product.DefaultImage.urls[1] alt=$product.DefaultImage.title title=$product.DefaultImage[1].title }
					{% endif %}
				</div>
				<span class="productRelationship_title">[[product.count]] x [[product.name_lang]]</span>
				<a href="{backendProductUrl product=$product}" onclick="Backend.Product.openProduct([[product.ID]]); return false;" class="openRelatedProduct" style="line-height: 3em;"></a>
			</fieldset>
			<div class="clear: both"></div>
		</span>
		</li>
	{/foreach}
	</ul>
</fieldset>
{% endif %}