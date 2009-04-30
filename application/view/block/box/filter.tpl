{if $filters}
<div class="box expandResults">
	<div class="title">
		<div>{t _expand}</div>
	</div>

	<div class="content filterGroup">
		<h4>{t _remove_filter}:</h4>
		<ul>
		{foreach from=$filters item=filter}
			<li><a href="{categoryUrl data=$category filters=$filters removeFilter=$filter}">{$filter.filterGroup.name_lang} {$filter.name_lang}</a></li>
		{/foreach}
		</ul>
	</div>
</div>
{/if}

{sect}{header}
<div class="box narrowResults">
	<div class="title">
		<div>{t _narrow_results}</div>
	</div>

	<div class="content">
{/header}{content}

		{include file="category/block/filterLinks.tpl" sectionFilters=$manGroup title=_by_brand allLink=$allManufacturers allTitle=_show_all_brands}
		{include file="category/block/filterLinks.tpl" sectionFilters=$priceGroup title=_by_price}

		{foreach from=$groups item="group"}
			{if $group.displayLocation == 0}
				{include file="category/block/filterLinks.tpl" sectionFilters=$group title=$group.name_lang allLink=$group.more allTitle=_show_all}
			{/if}
		{/foreach}

{/content}{footer}
	</div>
</div>
{/footer}{/sect}