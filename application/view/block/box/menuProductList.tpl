<ul class="compactProductList">
	{foreach from=$products item=product}
		<li>
			[[ partial("block/box/menuProductListItem.tpl") ]]
		</li>
	{/foreach}
</ul>