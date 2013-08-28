{loadJs}
{sect}{header}
<div class="filterTop" id="filterTop_{uniqid}">
<form action="{categoryUrl data=$category filters=$filters}" method="post" id="{uniqid last=true}" class="form-horizontal">
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
{if !'TOP_FILTER_RELOAD'|config}
	<input type="submit" class="submit" value="{tn _search}" />
{/if}
</form>
</div>

<script type="text/javascript">
	var filters = new Filter.SelectorMenu($("filterTop_{uniqid last=true}"), [[ config('TOP_FILTER_RELOAD') ]]);
</script>
{/footer}{/sect}