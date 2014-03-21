{% extends "layout/frontend.tpl" %}
{% block title %}
	[[ product.name() ]]
	{% set metaDescription = strip_tags(product.shortDescription()) %}
	{% set metaKeywords = product.keywords %}
{% endblock %}

{% block content %}

[[ render("product/images") ]]

[[ product.longDescription() ]]

{#
<div class="productIndex productCategory_[[product.Category.ID]] product_[[product.ID]]">

[[ partial("product/head.tpl") ]]

<div class="row" id="productNavigation">
	<div class="col-sm-12">
		{block PRODUCT-NAVIGATION}  {* product/block/images.tpl *}
	</div>
</div>

{% if config('PRODUCT_TABS') %}
	<ul class="nav nav-tabs" id="productTabs"></ul>
{% endif %}

<div id="productContent" class="productContent">
	{% if product.type == 2 %}
		[[ partial("product/bundle.tpl") ]]
	{% endif %}

	[[ partial("product/files.tpl") ]]

	[[ partial("product/details.tpl") ]]

	{% if config('PRODUCT_INQUIRY_FORM') %}
		[[ partial("product/contactForm.tpl") ]]
	{% endif %}

	[[ partial("product/ratingForm.tpl") ]]
	[[ partial("product/sendToFriendForm.tpl") ]]

	{% if !empty(reviews) %}
		<div id="reviewSection" class="productSection reviewSection">
			<h2>{t _reviews}<small>{t _tab_reviews}</small></h2>
			[[ partial("product/reviewList.tpl") ]]

			{% if product.reviewCount  > reviews|@count %}
				<a href="{link product/reviews id=product.ID}" class="readAllReviews">{maketext text="_read_all_reviews" params=product.reviewCount}</a>
			{% endif %}
		</div>
	{% endif %}
#}

</div>

{% endblock %}
