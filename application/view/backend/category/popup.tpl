{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/Product.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Category.css"}

{pageTitle}{t _select_category}{/pageTitle}

{include file="layout/backend/meta.tpl"}

{literal}
<style>
	body
	{
		background-image: none;
	}
</style>
{/literal}

<div id="popupCategoryContainer" class="treeContainer">
	
	<div style="font-weight: bold; padding: 5px; font-size: larger;">{t _select_category}:</div>
	
	<div id="categoryBrowser" class="treeBrowser"> </div>
	
	<fieldset class="controls" style="margin-top: 0.2em;">
		<input type="button" class="submit" id="select" value="{tn _move_products}" />
		{t _or}
		<a href="#cancel" id="cancel" class="cancel">{t _cancel}</a>
	</fieldset>
		   
</div>

{literal}
<script type="text/javascript">
	Backend.Category.links = {};
	Backend.Category.links.categoryRecursiveAutoloading = '{/literal}{link controller=backend.category action=xmlRecursivePath}{literal}';
	Backend.Category.links.categoryAutoloading = '{/literal}{link controller=backend.category action=xmlBranch}{literal}';		

	Backend.Category.PopupSelector.prototype.confirmationMsg = '{/literal}{t _confirm_move|escape}{literal}';
		
	Backend.Category.init();	
	
	Backend.Category.treeBrowser.setXMLAutoLoading(Backend.Category.links.categoryAutoloading); 
	Backend.Category.addCategories({/literal}{json array=$categoryList}{literal});
	
	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();
	Backend.Category.initPage();
	
	Backend.Category.loadBookmarkedCategory();   
</script>
{/literal}
	
</body>
</html>