{% if $bundleData %}
<div id="bundle" class="productSection">
	<h2>{t _bundle_includes}:</h2>
	<div class="bundleList">
		<ul class="compactProductList">
			{foreach from=$bundleData item=item}
				<li>
					{include file="block/box/menuProductListItem.tpl" product=$item.RelatedProduct productInfoTemplate="product/block/bundleCount.tpl"}
				</li>
			{/foreach}
		</ul>
	</div>
	<div class="highlight bundleInfo">
		<p>
			{t _regular_price}: <span class="price">[[bundleTotal]]</span>
		</p>
		<p>
			{t _bundle_price}: <span class="price bundlePrice">{$product.formattedPrice.$currency}</span> ({t _bundle_save} <span class="price percent">[[bundleSavingPercent]]%</span>)
		</p>

	</div>

	<div class="clear"></div>
</div>
{% endif %}