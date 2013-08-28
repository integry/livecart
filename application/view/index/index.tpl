{% extends "layout/frontend.tpl" %}

{% block title %}[[ config('STORE_HEADLINE') ]]{% endblock %}
{% block metaDescription %}[[ config('INDEX_META_DESCRIPTION') ]]{% endblock %}
{% block metaKeywords %}[[ config('INDEX_META_KEYWORDS') ]]{% endblock %}

{% block content %}
    <p class="important">Welcome on my awesome {{homepage}}.</p>
{% endblock %}

{#
	{block HOME-PAGE-TOP}

	{if 'HOME_PAGE_SUBCATS'|config}
		{include file="category/subcategoriesColumns.tpl"}
	{/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>
		{include file="category/productListLayout.tpl" layout='FEATURED_LAYOUT'|config|default:$layout products=$subCatFeatured}
	{/if}

	{if $news}
		{include file="index/latestNews.tpl"}
	{/if}

	{include file="category/categoryProductList.tpl"}
#}