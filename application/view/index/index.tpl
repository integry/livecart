{% extends "layout/frontend.tpl" %}

{% block title %}[[ config('STORE_HEADLINE') ]]{% endblock %}
{% block metaDescription %}[[ config('INDEX_META_DESCRIPTION') ]]{% endblock %}
{% block metaKeywords %}[[ config('INDEX_META_KEYWORDS') ]]{% endblock %}

{% block content %}
    <p class="important">Welcome on my awesome {{homepage}}.</p>
{% endblock %}

{#
	{block HOME-PAGE-TOP}

	{% if config('HOME_PAGE_SUBCATS') %}
		[[ partial("category/subcategoriesColumns.tpl") ]]
	{% endif %}

	{% if !empty(subCatFeatured) %}
		<h2>{t _featured_products}</h2>
		[[ partial('category/productListLayout.tpl', ['layout': config('FEATURED_LAYOUT')|default:layout, 'products': subCatFeatured]) ]]
	{% endif %}

	{% if !empty(news) %}
		[[ partial("index/latestNews.tpl") ]]
	{% endif %}

	[[ partial("category/categoryProductList.tpl") ]]
#}