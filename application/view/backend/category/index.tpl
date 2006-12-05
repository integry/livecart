{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/Category.js"}
{includeJs file="library/SectionExpander.js"}

{includeCss file="library/TabControl.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{include file="layout/header.tpl"}

{pageTitle}Category manager. Selected: <span id="activeCategoryPath" style="font-weight: normal">Electronics > New Category</span>{/pageTitle}

	<div id="catgegoryContainer" style="float:left; width: 260px;">
		<div id="categoryBrowser" style="padding: 10px; border: 1px solid #ccc; background-color: #f1f1f1;">
		</div>
		<div>
			<a href="javascript:Backend.Category.createNewBranch();">Create a new sub-category</a>
			<br/>
			<a href="#" onclick="if (confirm('Are you sure you want to remove this category?')) Backend.Category.removeBranch(); return false;">Remove selected category</a>
		</div>
	</div>

	<div id="managerContainer" style="margin-left: 270px; height: 100%;">
		<div id="tabContainer">
			<ul id="tabList">
				<li id="tabProducts" class="tab active"><a href="{link controller=backend.product action=index id=%id%}">Products</a></li>
				<li id="tabMainDetails" class="tab inactive"><a href="{link controller=backend.category action=form id=%id%}">Category Details</a></li>
				<li id="tabFields" class="tab inactive"><a href="{link controller=backend.specField action=index id=%id%}">Fields</a></li>
				<li id="tabFilters" class="tab inactive"><a href="{link controller=backend.filter action=index id=%id%}">Filters</a></li>
				<li id="tabImages" class="tab inactive"><a href="{link controller=backend.image action=index id=%id%}">Images</a></li>
				<li id="tabArticles" class="tab inactive"><a href="{link controller=backend.image action=index id=%id%}">Articles</a></li>
			</ul>
		</div>
		<div id="sectionContainer">

		</div>
	</div>

<script type="text/javascript">
	Backend.Category.init();
{foreach from=$categoryList item=category}
	Backend.Category.treeBrowser.insertNewItem({$category.parent},{$category.ID} , '{$category.name}', 0, 0, 0, 0, "SELECT");
{/foreach}
	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();

	/**
	 * URL assisgment for internal javascript requests
	 */
	var newNodeUrl = '{link controller=backend.category action=create id=%id%}';
	var removeNodeUrl = '{link controller=backend.category action=remove id=%id%}';

</script>

<div id="specFieldSection"></div>

{include file="layout/footer.tpl"}