{% extends "layout/frontend.tpl" %}

{includeJs file="library/lightbox/lightbox.js"}
{includeCss file="library/lightbox/lightbox.css"}

{% set metaDescription = $product.shortDescription_lang %}
{% set metaKeywords = $product.keywords_lang %}
{% block title %}[[product.name_lang]]{% endblock %}

<div class="reviewIndex productCategory_[[product.Category.ID]] product_[[product.ID]]">

[[ partial("product/layout.tpl") ]]

{% block content %}

	<div class="returnToCategory">
		<a href="{productUrl product=$product}" class="returnToCategory">[[product.name_lang]]</a>
	</div>

	<h1>{maketext text="_reviews_for" params=$product.name_lang}</h1>

	<div class="resultStats">
		[[ partial("product/ratingSummary.tpl") ]]
		<div class="pagingInfo">
			{maketext text=_showing_reviews params="`$offsetStart`,`$offsetEnd`,`$product.reviewCount`"}
		</div>
		<div class="clear"></div>
	</div>

	<div class="clear"></div>

	[[ partial("product/reviewList.tpl") ]]

	{% if $product.reviewCount > $perPage %}
		{paginate current=$page count=$product.reviewCount perPage=$perPage url=$url}
	{% endif %}

	[[ partial("product/ratingForm.tpl") ]]

{% endblock %}


</div>
