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

{pageTitle}{t _select_product}{/pageTitle}

{include file="layout/backend/meta.tpl"}

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
		
	<div id="confirmations"></div>
</div>
<div id="activeCategoryPath"></div>
 
<div id="managerContainer" class="treeManagerContainer">
	<div id="tabContainer" class="tabContainer">
	<ul id="tabList" class="tabList tabs">

		<li id="tabProducts" class="tab active">
			<a href="{link controller=backend.product action=index id=_id_}?{$filters}">{t _products}</a>
			<span> </span>
			<span class="tabHelp">products</span>
		</li>

		<li id="tabMainDetails" class="tab inactive">
			<a href="{link controller=backend.category action=form id=_id_}">{t _category_details}</a>
			<span> </span>
			<span class="tabHelp">cat.details</span>
		</li>
		
		<li id="tabFields" class="tab inactive">
			<a href="{link controller=backend.specField action=index id=_id_}">{t _attributes}</a>
			<span> </span>
			<span class="tabHelp">cat.attr</span>
		</li>
		
		<li id="tabFilters" class="tab inactive">
			<a href="{link controller=backend.filterGroup action=index id=_id_}">{t _filters}</a>
			<span> </span>
			<span class="tabHelp">cat.filters</span>
		</li>
		
		<li id="tabImages" class="tab inactive">
			<a href="{link controller=backend.categoryImage action=index id=_id_}">{t _images}</a>
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
			<input name="shipment" type="radio" value="{$shipment.ID}" id="shipment{$shipment.ID}" class="checkbox" {if !$checked}checked="checked"{/if}>
			<label for="shipment{$shipment.ID}" class="checkbox"><b>{t _shipment} #{$shipment.ID}</b> ({$shipment.ShippingService.name_lang} - {$shipment.formatted_totalAmount})</label>
		</fieldset>
		
	{/foreach}
</form>

{literal}
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
	Backend.Category.links.categoryRecursiveAutoloading = '{/literal}{link controller=backend.category action=xmlRecursivePath}{literal}';
	Backend.Category.links.countTabsItems = '{/literal}{link controller=backend.category action=countTabsItems id=_id_}{literal}';
	Backend.Category.links.categoryAutoloading = '{/literal}{link controller=backend.category action=xmlBranch}{literal}';		
		
	Backend.Category.init();	
	
	Backend.Category.treeBrowser.setXMLAutoLoading(Backend.Category.links.categoryAutoloading); 
	Backend.Category.addCategories({/literal}{json array=$categoryList}{literal});
	
	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();
	Backend.Category.initPage();
	
	Backend.Category.loadBookmarkedCategory();
	
	{/literal}
		{allowed role="product"}
			Backend.Product.productsMiscPermision = true;
		{/allowed}
	{literal}
</script>
{/literal}
	
</body>
</html>