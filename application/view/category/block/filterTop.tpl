{sect}{header}
<div class="filterTop">
{/header}{content}

	{if 'TOP_FILTER_PRICE'|config}
		{include file="category/block/filterSelect.tpl" sectionFilters=$priceGroup title=_by_price}
	{/if}

	{if 'TOP_FILTER_MANUFACTURER'|config}
		{include file="category/block/filterSelect.tpl" sectionFilters=$manGroup title=_by_brand}
	{/if}

	{foreach from=$groups item="group"}
		{if $group.displayLocation == 1}
			{include file="category/block/filterSelect.tpl" sectionFilters=$group title=$group.name_lang allLink=$group.more allTitle=_show_all}
		{/if}
	{/foreach}

{/content}{footer}
</div>
{/footer}{/sect}