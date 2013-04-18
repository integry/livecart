<div class="row" id="productHead">
	<div class="col-span-6">
		{block PRODUCT-IMAGES}  {* product/block/images.tpl *}
	</div>
	<div class="col-span-6">
		<h1>{$product.name_lang}</h1>
		{block PRODUCT-ATTRIBUTE-SUMMARY}	{* product/block/attributeSummary.tpl *}
		{block PRODUCT-SUMMARY} {* product/block/summary.tpl *}
	</div>
</div>

<div class="row" id="productNavigation">
	<div class="col-span-12">
		{block PRODUCT-NAVIGATION}  {* product/block/images.tpl *}
	</div>
</div>
