{if $bundledProducts}
<div id="bundle">
	<h2>{t _bundle_includes}:</h2>
	<div class="bundleList">
		{include file="block/box/menuProductList.tpl" products=$bundledProducts}
	</div>
	<div class="highlight bundleInfo">
		<p>
			{t _regular_price}: <span class="price">{$bundleTotal}</span>
		</p>
		<p>
			{t _bundle_price}: <span class="price bundlePrice">{$product.formattedPrice.$currency}</span> ({t _bundle_save} <span class="price percent">{$bundleSavingPercent}%</span>)
		</p>

	</div>

	<div class="clear"></div>
</div>
{/if}