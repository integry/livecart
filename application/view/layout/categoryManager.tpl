{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="backend/categoryManager.js"}

{includeCss file="base.css"}
{includeCss file="stat.css"}

{assign var="TITLE" value="Product Category Management"}

{include file="layout/header.tpl"}

	<div id="catgegoryContainer" style="float:left; width: 260px;">

		<div id="categoryBrowser" style="padding: 10px; border: 1px solid #ccc; background-color: #f1f1f1;">
		</div>
	</div>
	
	<script type="text/javascript">
		LiveCart.CategoryManager.init();
	</script>
	
	<div id="managerContainer" style="margin-left: 270px;">
		<ul id="tabContainer">
			<li>Main details</li>
			<li>Fields</li>
			<li>Filters</li>
			<li>Subcategory Order</li>
			<li>Permissions</li>
			<li>Images</li>
			<li>Articles</li>
		</ul>


		<div id="sectionContainer">
			<div id="mainDetailsSection">
				{$ACTION_VIEW}
			</div>
			
			<div id="fieldsSection" style="display: none;"></div>
			
			<div id="filtersSection" style="display: none"></div>
			
		</div>
	</div>
	
{include file="layout/footer.tpl"}