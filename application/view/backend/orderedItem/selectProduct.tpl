{includeJs file="library/livecart.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/Product.js"}

{includeCss file="library/ActiveGrid.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/Product.css"}
{includeCss file="backend/ProductRelatedSelectProduct.css"}

{% title %}{t _select_product}{% endblock %}

[[ partial("layout/backend/meta.tpl") ]]

<a id="help" href="#" target="_blank" style="display: none;">Help</a>
<div id="catgegoryContainer" class="treeContainer">

	<div>
		<ul class="menu popup">
			<li class="done">
				<a class="menu" href="#" onclick="window.close(); return false;">
					{t _done_adding_products}
				</a>
			</li>
		</ul>
	</div>
	<div id="categoryBrowser" class="treeBrowser"> </div>
</div>
<div id="activeCategoryPath"></div>

<div id="managerContainer" class="treeManagerContainer">
	<div id="tabContainer" class="tabContainer">
	<ul id="tabList" class="tabList tabs">

		<li id="tabProducts" class="tab active">
			<a href="[[ url("backend.product/index/_id_") ]]?[[filters]]">{t _products}</a>
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

<form id="availableShipments">
	<h2>{t _select_shippment}</h2>
	{foreach name="shipments" item="shipment" from=$shipments}
		<fieldset class="error">
			<input name="shipment" type="radio" value="[[shipment.ID]]" id="shipment[[shipment.ID]]" class="checkbox" {% if empty(checked) %}checked="checked"{% endif %}>
			<label for="shipment[[shipment.ID]]" class="checkbox"><b>{t _shipment} #[[shipment.ID]]</b> ([[shipment.ShippingService.name()]] - [[shipment.formatted_totalAmount]])</label>
		</fieldset>

	{/foreach}
</form>


<script type="text/javascript">
	if(window.opener)
	{
		var checked = false;
		$("availableShipments").getElementsBySelector("fieldset").each(function(fieldset)
		{
			var radio = fieldset.down("input[type=radio]");
			var shipmentID = radio.id.replace(/shipment(\d+)/, "$1");
			var orderID = window.opener.Backend.CustomerOrder.Editor.prototype.CurrentId;

			if(!window.opener.$$("#tabOrderProducts_" + orderID + "Content .shippableShipments #orderShipmentsItems_list_" + orderID + "_" + shipmentID).size())
			{
				Element.remove(fieldset);
				return;
			}

			if(!checked)
			{
				radio.checked = true;
			}
		});

		if($("availableShipments").getElementsBySelector("fieldset").size() <= 1)
		{
			checked = true;
			$("availableShipments").hide();
		}
	}

	Backend.Category.links = {};
	Backend.Category.links.categoryRecursiveAutoloading = '[[ url("backend.category/xmlRecursivePath") ]]';
	Backend.Category.links.countTabsItems = '[[ url("backend.category/countTabsItems/_id_") ]]';
	Backend.Category.links.categoryAutoloading = '[[ url("backend.category/xmlBranch") ]]';

	Backend.Category.init();

	Backend.Category.treeBrowser.setXMLAutoLoading(Backend.Category.links.categoryAutoloading);
	Backend.Category.addCategories({json array=$categoryList});

	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();
	Backend.Category.initPage();

	Backend.Category.loadBookmarkedCategory();


		{allowed role="product"}
			Backend.Product.productsMiscPermision = true;
		{/allowed}

</script>


</body>
</html>