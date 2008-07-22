{if !$manufacturerFilter}
	<h1>{$category.name_lang}{if $searchQuery} &gt;&gt; "<span class="keywords">{$searchQuery}</span>"{/if}</h1>
{else}
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
				<img src="{$products.0.Manufacturer.DefaultImage.paths.3}" class="manufacturerImage" />
			{/if}
		</fieldset>
	{/if}
{/if}
