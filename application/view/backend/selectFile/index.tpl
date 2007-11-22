{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="backend/SelectFile.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/SelectFile.css"}

{pageTitle}{t _select_file}{/pageTitle}

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

<div id="fileContainer" style="margin-left: 300px; height: 200px; border: 1px solid black;">
    
{activeGrid 
	prefix="products" 
	id=0 
	role="product.mass" 
	controller="backend.product" action="lists" 
	displayedColumns=$displayedColumns 
	availableColumns=$availableColumns 
	totalCount=0
	filters=$filters
	container="tabProducts"
}    
    
</div>

{literal}
<script type="text/javascript">
	Backend.SelectFile.links = {};
	Backend.SelectFile.links.categoryRecursiveAutoloading = '{/literal}{link controller=backend.selectFile action=xmlRecursivePath}{literal}';
	Backend.SelectFile.links.categoryAutoloading = '{/literal}{link controller=backend.selectFile action=xmlBranch}{literal}';		

	Backend.SelectFile.init();
	Backend.SelectFile.addCategories({/literal}{json array=$root}{literal});
	Backend.SelectFile.addCategories({/literal}{json array=$directoryList}{literal});
	
	Backend.SelectFile.treeBrowser.setXMLAutoLoading(Backend.SelectFile.links.categoryAutoloading); 

	
	Backend.SelectFile.activeCategoryId = Backend.SelectFile.treeBrowser.getSelectedItemId();
	Backend.SelectFile.initPage();
	
	Backend.SelectFile.loadBookmarkedCategory();   

</script>
{/literal}
	
</body>
</html>