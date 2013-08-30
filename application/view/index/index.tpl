{% extends "layout/frontend.tpl" %}

{% block title %}[[ config('STORE_HEADLINE') ]]{% endblock %}
{% block metaDescription %}[[ config('INDEX_META_DESCRIPTION') ]]{% endblock %}
{% block metaKeywords %}[[ config('INDEX_META_KEYWORDS') ]]{% endblock %}

{% block content %}
    <p class="important">Welcome on my awesome {{homepage}}.</p>
{% endblock %}

{#
	{block HOME-PAGE-TOP}

	{% if 'HOME_PAGE_SUBCATS'|config %}
		[[ partial("category/subcategoriesColumns.tpl") ]]
	{% endif %}

	{% if !empty(subCatFeatured) %}
		<h2>{t _featured_products}</h2>
		[[ partial('category/productListLayout.tpl', ['layout': 'FEATURED_LAYOUT'|config|default:$layout, 'products': subCatFeatured]) ]]
	{% endif %}

	{% if !empty(news) %}
		[[ partial("index/latestNews.tpl") ]]
	{% endif %}

	[[ partial("category/categoryProductList.tpl") ]]
#}