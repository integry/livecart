{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/categoryManager.js"}
{includeJs file="library/tabControll.js"}

{includeCss file="base.css"}
{includeCss file="stat.css"}
{includeCss file="form.css"}
{includeCss file="tabControll.css"}
{includeCss file="backend/dhtmlxtree/dhtmlXTree.css"}
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
				<li id="tabMainDetails" class="tab active"><a href="{link controller=backend.category action=form}">Main Details</a></li>
				<li id="tabFields" class="tab inactive"><a href="{link controller=backend.specField action=index}">Fields</a></li>
				<li id="tabFilters" class="tab inactive">Filters</li>
				<li id="tabPermissions" class="tab inactive">Permissions</li>
				<li id="tabImages" class="tab inactive">Images</li>
				<li id="tabArticles" class="tab inactive">Articles</li>
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
</script>

<div id="specFieldSection"></div>

{include file="layout/footer.tpl"}