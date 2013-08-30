{includeJs file="library/livecart.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/Product.js"}

{includeCss file="library/ActiveGrid.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/Product.css"}
{includeCss file="backend/ProductRelatedSelectProduct.css"}

[[ partial("backend/category/loadJsTree.tpl") ]]

{% block title %}{t _select_product}{{% endblock %}

[[ partial("layout/backend/meta.tpl") ]]

<a id="help" href="#" target="_blank" style="display: none;">Help</a>

<div id="catgegoryContainer" class="treeContainer">

	<div style="margin-bottom: 5px; text-align: center;">
		<ul class="menu">
			<li class="done">
				<a class="menu" href="#" onclick="window.close(); return false;">
					{t _done_adding}
				</a>
			</li>
		</ul>
	</div>

	<div id="categoryBrowser" class="treeBrowser"> </div>

	<div id="confirmations">
		<div id="redZone">
			<div id="productRelationshipCreateFailure" class="redMessage" style="display: none;">
				{img class="closeMessage" src="image/silk/cancel.png"}
				<div>{t _could_not_create_product_relationship}</div>
			</div>
		</div>
		<div id="yellowZone">
			<div id="productRelationshipCreated" class="yellowMessage" style="display: none;">
				{img class="closeMessage" src="image/silk/cancel.png"}
				<div>{t _a_relationship_between_products_was_successfully_created}</div>
			</div>
		</div>
	</div>

</div>
<div id="activeCategoryPath"></div>

<div id="managerContainer" class="treeManagerContainer">
	<div id="tabContainer" class="tabContainer">
	<ul id="tabList" class="tabList tabs">

		<li id="tabProducts" class="tab active">
			<a href="[[ url("backend.product/index/_id_") ]]">{t _products}</a>
			<span> </span>
			<span class="tabHelp">products</span>
		</li>

		<li id="tabMainDetails" class="tab inactive">
			<a href="[[ url("backend.category/form/_id_") ]]">{t _category_details}</a>
			<span> </span>
			<span class="tabHelp">cat.details</span>
		</li>

		<li id="tabFields" class="tab inactive">
			<a href="[[ url("backend.specField/index/_id_") ]]">{t _attributes}</a>
			<span> </span>
			<span class="tabHelp">cat.attr</span>
		</li>

		<li id="tabFilters" class="tab inactive">
			<a href="[[ url("backend.filterGroup/index/_id_") ]]">{t _filters}</a>
			<span> </span>
			<span class="tabHelp">cat.filters</span>
		</li>

		<li id="tabImages" class="tab inactive">
			<a href="[[ url("backend.categoryImage/index/_id_") ]]">{t _images}</a>
			<span> </span>
			<span class="tabHelp">cat.images</span>
		</li>
	</ul>
	</div>
	<div id="sectionContainer" class="sectionContainer maxHeight  h--50"> </div>
</div>


<script type="text/javascript">
	Backend.Category.links = {};
	Backend.Category.links.countTabsItems = '[[ url("backend.category/countTabsItems/_id_") ]]';

	Backend.Category.init({json array=$categoryList});


		{allowed role="product"}
			Backend.Product.productsMiscPermision = true;
		{/allowed}

</script>


</body>
</html>