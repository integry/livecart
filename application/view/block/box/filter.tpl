{assign var=FILTER_STYLE value='FILTER_STYLE'|config}

{if 'FILTER_STYLE_CHECKBOXES' == $FILTER_STYLE}
	{assign var=FILTER_STYLE_TEMPLATE value='category/block/filterCheckboxes.tpl'}
{else}
	{assign var=FILTER_STYLE_TEMPLATE value='category/block/filterLinks.tpl'}
{/if}


{if $filters && $FILTER_STYLE == 'FILTER_STYLE_LINKS'}
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

	{if 'FILTER_STYLE_CHECKBOXES' == $FILTER_STYLE}
		<form id='multipleChoiceFilterForm' action="{categoryUrl data=$category}" method="post">

		<input type="hidden" value="{categoryUrl data=$category}" id="formActionWithoutFilters" /> {* for Filter.reset() *}
		
		<input type="submit" value="{t _filter}" />
		<a href="javascript:void(0);" onclick="Filter.reset();" class="cancel">{t _clear}</a>
		
	{/if}
		{include file=$FILTER_STYLE_TEMPLATE sectionFilters=$manGroup title=_by_brand allLink=$allManufacturers allTitle=_show_all_brands}
		{include file=$FILTER_STYLE_TEMPLATE sectionFilters=$priceGroup title=_by_price}

		{foreach from=$groups item="group"}
			{if $group.displayLocation == 0}
				{include file=$FILTER_STYLE_TEMPLATE sectionFilters=$group title=$group.name_lang allLink=$group.more allTitle=_show_all}
			{/if}
		{/foreach}

	{if 'FILTER_STYLE_CHECKBOXES' == $FILTER_STYLE}
	
		<input type="submit" value="{t _filter}" />
		<a href="javascript:void(0);" onclick="Filter.reset();" class="cancel">{t _clear}</a>

		</form>
	{/if}
	
{/content}{footer}
	</div>
</div>
{/footer}{/sect}