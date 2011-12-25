{if $manufacturerFilter && ($appliedFilters|@count == 1) && ($currentPage == 1)}
	<h1>{$manufacturerFilter.name_lang}</h1>

	{if $products.0.Manufacturer.attributes || $products.0.Manufacturer.DefaultImage.ID}
		<fieldset class="container" style="margin-bottom: 1em;">
			{if $products.0.Manufacturer.attributes}
				<div id="productSpecification" class="manufacturerAttributes">
					<table class="productTable">
						{include file="product/specificationTableBody.tpl" attributes=$products.0.Manufacturer.attributes field=EavField group=EavFieldGroup}
					</table>
				</div>
			{/if}

			{if $products.0.Manufacturer.DefaultImage.ID}
				<img src="{$products.0.Manufacturer.DefaultImage.urls.3}" alt="{$products.0.Manufacturer.name_lang}" class="manufacturerImage" />
			{/if}
		</fieldset>
	{/if}
{else}
	<h1>{$category.name_lang}{if $searchQuery} &gt;&gt; "<span class="keywords">{$searchQuery}</span>"{/if}</h1>
{/if}

{if 'DISPLAY_CATEGORY_DESC'|config && $category.description_lang}
	<div class="descr categoryDescr">
		{$category.description_lang}
	</div>
{/if}

{block RELATED_CATEGORIES}