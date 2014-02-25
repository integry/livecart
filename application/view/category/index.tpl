{% extends "layout/frontend.tpl" %}

{#
{% block title %}{$category.pageTitle_lang|default:$category.name_lang}{% endblock %}
{% set metaKeywords = $category.keywords_lang %}
#}

{% block content %}
<div class="categoryIndex category_[[category.ID]]">

[[ partial('block/content-start.tpl', ['hideTitle': true]) ]]

	{#
	[[ partial("category/head.tpl") ]]

	{% if $allFilters.filters %}
		[[ partial("category/allFilters.tpl") ]]
	{% endif %}

	{% if !empty(foundCategories) %}
		[[ partial("category/foundCategories.tpl") ]]
	{% endif %}

	{% if !empty(modelSearch) %}
		[[ partial("search/block/allResults.tpl") ]]
	{% endif %}

	{% if !empty(categoryNarrow) %}
		[[ partial("category/narrowByCategory.tpl") ]]
	{% elseif !$searchQuery && $subCategories && !'HIDE_SUBCATS'|config %}
		[[ partial("category/subcategoriesColumns.tpl") ]]
	{% endif %}

	{% if $searchQuery && !$products %}
		<p class="notFound">
			{t _not_found}
		</p>
	{% endif %}

	{% if $appliedFilters && !$products %}
		<p class="notFound">
			<span class='notFoundMain'>{t _no_products}</span>
		</p>
	{% endif %}

	{% if !$searchQuery && 1 == $currentPage %}
		{block PRODUCT_LISTS}
	{% endif %}

	{% if !empty(subCatFeatured) %}
		<h2>{t _featured_products}</h2>

		[[ partial('category/productListLayout.tpl', ['layout': 'FEATURED_LAYOUT'|config|default:$layout, 'products': subCatFeatured]) ]]
	{% endif %}

	{block FILTER_TOP}

	[[ partial("category/categoryProductList.tpl") ]]
	#}

{% endblock %}
