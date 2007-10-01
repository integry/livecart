{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/Product.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Category.css"}

{pageTitle}{t _select_category}{/pageTitle}

{include file="layout/backend/meta.tpl"}

<div id="popupCategoryContainer" class="treeContainer">
	
    <div id="categoryBrowser" class="treeBrowser"> </div>
    
    <fieldset class="controls">
        <input type="button" class="submit" id="select" value="{tn _select_category}" />
        {t _or}
        <a href="#cancel" onclick="window.close(); return false;" class="cancel">{t _cancel}</a>
    </fieldset>
           
</div>

{literal}
<script type="text/javascript">
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
</script>
{/literal}
    
</body>
</html>