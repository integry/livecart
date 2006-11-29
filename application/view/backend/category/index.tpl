{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Category.js"}
{includeJs file="library/TabControl.js"}

{includeCss file="library/TabControl.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{include file="layout/header.tpl"}

{pageTitle}Category manager. Selected: <span id="activeCategoryPath" style="font-weight: normal">Electronics > New Category</span>{/pageTitle}

	<div id="catgegoryContainer" style="float:left; width: 260px;">
		<div id="categoryBrowser" style="padding: 10px; border: 1px solid #ccc; background-color: #f1f1f1;">
		</div>
		<div>
			<a href="">Create a new category</a>
			<br/>
			<a href="{link controller=backend.category action=remove}">Remove selected category</a>
		</div>
	</div>

	<div id="managerContainer" style="margin-left: 270px; height: 100%;">
		<div id="tabContainer">
			<ul id="tabList">
				<li id="tabMainDetails" class="tab active"><a href="{link controller=backend.category action=form id=%id%}">Products</a></li>
				<li id="tabMainDetails" class="tab inactive"><a href="{link controller=backend.category action=form id=%id%}">Category Details</a></li>
				<li id="tabFields" class="tab inactive"><a href="{link controller=backend.specField action=index id=%id%}">Fields</a></li>
				<li id="tabFilters" class="tab inactive"><a href="{link controller=backend.filter action=index id=%id%}">Filters</a></li>
				<li id="tabPermissions" class="tab inactive"><a href="{link controller=backend.permission id=%id% action=index}">Permissions</a></li>
				<li id="tabImages" class="tab inactive"><a href="{link controller=backend.image action=index id=%id%}">Images</a></li>
				<li id="tabArticles" class="tab inactive"><a href="{link controller=backend.image action=index id=%id%}">Articles</a></li>
			</ul>
		</div>
		<div id="sectionContainer">
			<div id="tabMainDetailsContent"></div>
			<div id="tabFieldsContent"></div>
			<div id="tabFiltersContent"></div>
			<div id="tabPermissionsContent"></div>
			<div id="tabImagesContent"></div>
			<div id="tabArticlesContent"></div>
		</div>
	</div>

<script type="text/javascript">
	LiveCart.CategoryManager.init();
{foreach from=$categoryList item=category}
	LiveCart.CategoryManager.treeBrowser.insertNewItem({$category.parent},{$category.ID} , '{$category.name}', 0, 0, 0, 0, "CHILD");
{/foreach}
</script>

<div id="specFieldSection"></div>

{include file="layout/footer.tpl"}