{includeJs file="library/livecart.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/jscolor/jscolor.js"}
{includeJs file="library/TabControl.js"}

{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}

{includeJs file="backend/Product.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/SpecField.js"}
{includeJs file="backend/Filter.js"}
{includeJs file="backend/ObjectImage.js"}
{includeJs file="backend/Product.js"}
{includeJs file="backend/abstract/ProductListCommon.js"}
{includeJs file="backend/RelatedProduct.js"}
{includeJs file="backend/ProductCategory.js"}
{includeJs file="backend/CategoryRelationship.js"}
{includeJs file="backend/ProductList.js"}
{includeJs file="backend/ProductFile.js"}
{includeJs file="backend/ProductOption.js"}
{includeJs file="backend/RecurringProductPeriod.js"}
{includeJs file="backend/ProductBundle.js"}
{includeJs file="backend/ProductVariation.js"}
{includeJs file="backend/RatingType.js"}
{includeJs file="backend/Review.js"}

{include file="backend/eav/includes.tpl"}

{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/Product.css"}
{includeCss file="backend/SpecField.css"}
{includeCss file="backend/ProductRelationship.css"}
{includeCss file="backend/RecurringProductPeriod.css"}
{includeCss file="backend/ProductBundle.css"}
{includeCss file="backend/ProductCategory.css"}
{includeCss file="backend/ProductFile.css"}
{includeCss file="backend/ProductOption.css"}
{includeCss file="backend/ProductVariation.css"}
{includeCss file="backend/Filter.css"}
{includeCss file="backend/CategoryImage.css"}
{includeCss file="backend/RatingType.css"}
{includeCss file="backend/Review.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="backend/Eav.css"}

{include file="backend/category/loadJsTree.tpl"}

{pageTitle help="cat"}<span id="activeCategoryPath"></span><span id="activeProductPath" style="display: none;"><span id="productCategoryPath"></span><span id="activeProductName"></span></span><span style="display: none;">{t _products_and_categories}</span>{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="specField_item_blank" class="dom_template">{include file="backend/specField/form.tpl"}</div>
<div id="specField_group_blank" class="dom_template">{include file="backend/specField/group.tpl"}</div>
<div id="filter_item_blank" class="dom_template">{include file="backend/filterGroup/form.tpl"}</div>
<div id="productFileGroup_item_blank">{include file="backend/productFileGroup/form.tpl"}</div>
<div id="productFile_item_blank">{include file="backend/productFile/form.tpl"}</div>
<div id="productOption_item_blank" class="dom_template">{* include file="backend/productOption/form.tpl" *}</div>

<div ng-controller="CategoryController" ng-init="setTree({$categoryList|escape})">
	<div class="treeContainer">
		{include file="block/backend/tree.tpl" sortable=true}

		{allowed role="category.create,category.remove,category.sort"}
			{t _with_selected_category}:

			<ul id="categoryBrowserActions" class="verticalMenu">

				{allowed role="category.create"}
					<li class="addTreeNode">
						<a href="#" id="createNewCategoryLink">
							{t _create_subcategory}
						</a>
					</li>
				{/allowed}

				{allowed role="category.remove"}
					<li class="removeTreeNode">
						<a href="#" id="removeCategoryLink">
							{t _remove_category}
						</a>
					</li>
				{/allowed}
			</ul>
		{/allowed}
	</div>

	<div id="managerContainer" class="treeManagerContainer maxHeight h--60">
		<div ng-repeat="category in categories" ng-show="category.id == activeID">
			<tabset>
				{* <tab-route heading="{t _products}" template="{link controller="backend.product" action="index"}" route="{{'backend.product/' + activeID + '/index'}}"></tab-route> *}
				<tab-route heading="{t _category_details}" template="{link controller="backend.category" action="form"}" route="{{route('backend.category', 'category', {id: category.id})}}"></tab-route>
			</tabset>
		</div>
	</div>

	{*

	<div id="categoryTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		<div id="tabContainer" class="tabContainer">
			{tabControl id="tabList"}
				{tab id="tabProducts" role="product" help="products"}<a href="{link controller="backend.product" action=index id=_id_ }">{t _products}</a>{/tab}
				{tab id="tabMainDetails" role="category" help="categories.details"}<a href="{link controller="backend.category" action=form id=_id_}">{t _category_details}</a>{/tab}
				{tab id="tabFields" role="category" help="categories.attributes"}<a href="{link controller="backend.specField" action=index id=_id_}">{t _attributes}</a>{/tab}
				{tab id="tabFilters" role="filter" help="categories.filters"}<a href="{link controller="backend.filterGroup" action=index id=_id_}">{t _filters}</a>{/tab}
				{tab id="tabImages" role="category" help="categories.images"}<a href="{link controller="backend.categoryImage" action=index id=_id_}">{t _images}</a>{/tab}
				{tab id="tabOptions" role="option" help="categories"}<a href="{link controller="backend.productOption" action=index id=_id_ query="category=true"}">{t _options}</a>{/tab}
				{tab id="tabRatingCategories" role="ratingcategory" help="categories" hidden=true}<a href="{link controller="backend.ratingType" action=index id=_id_}">{t _rating_categories}</a>{/tab}
				{tab id="tabReviews" role="ratingcategory" help="categories" hidden=true}<a href="{link controller="backend.review" action=index id=_id_ query="category=true"}">{t _reviews}</a>{/tab}
				{tab id="tabProductLists" role="ratingcategory" help="categories" hidden=true}<a href="{link controller="backend.productList" action=index id=_id_}">{t _product_lists}</a>{/tab}
				{tab id="tabRelatedCategory" role="category" help="categories" hidden=true}<a href="{link controller="backend.categoryRelationship" action=index id=_id_}">{t _related_categories}</a>{/tab}
			{/tabControl}
		</div>
		<div id="sectionContainer" class="ui-tabs-panel ui-widget-content ui-corner-bottom sectionContainer maxHeight  h--50">
		</div>
	</div>
	<div id="addProductContainer" style="display: none;"></div>
	*}

</div>

<script type="text/javascript">
	{allowed role="category.sort"}
		Backend.Category.allowSorting = true;
	{/allowed}

	{allowed role="product"}
		Backend.Product.productsMiscPermision = true;
	{/allowed}

	Backend.showContainer('managerContainer');

	//Backend.Category.init({json array=$categoryList});

	//CategoryTabControl.prototype.loadCategoryTabsCount({json array=$allTabsCount});
</script>

{include file="backend/product/tabs.tpl"}

<script>
	Backend.Category.loadBookmarkedProduct();
</script>

<div id="specFieldSection"></div>

{include file="layout/backend/footer.tpl"}