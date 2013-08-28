{% block title %}{$category.pageTitle_lang|default:$category.name_lang}{{% endblock %}
{assign var="metaDescription" value=$category.description_lang|@strip_tags}
{% set metaKeywords = $category.keywords_lang %}

<div class="categoryIndex category_[[category.ID]]">



{include file="block/content-start.tpl" hideTitle=true}

	[[ partial("category/head.tpl") ]]

	{if $allFilters.filters}
		[[ partial("category/allFilters.tpl") ]]
	{/if}

	{if $foundCategories}
		[[ partial("category/foundCategories.tpl") ]]
	{/if}

	{if $modelSearch}
		[[ partial("search/block/allResults.tpl") ]]
	{/if}

	{if $categoryNarrow}
		[[ partial("category/narrowByCategory.tpl") ]]
	{elseif !$searchQuery && $subCategories && !'HIDE_SUBCATS'|config}
		[[ partial("category/subcategoriesColumns.tpl") ]]
	{/if}

	{if $searchQuery && !$products}
		<p class="notFound">
			{t _not_found}
		</p>
	{/if}

	{if $appliedFilters && !$products}
		<p class="notFound">
			<span class='notFoundMain'>{t _no_products}</span>
		</p>
	{/if}

	{if !$searchQuery && 1 == $currentPage}
		{block PRODUCT_LISTS}
	{/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>

		{include file="category/productListLayout.tpl" layout='FEATURED_LAYOUT'|config|default:$layout products=$subCatFeatured}
	{/if}

	{block FILTER_TOP}

	[[ partial("category/categoryProductList.tpl") ]]

	<script type="text/javascript">
		jQuery(document).ready(Frontend.initCategory);
	</script>

{% endblock %}


</div>
