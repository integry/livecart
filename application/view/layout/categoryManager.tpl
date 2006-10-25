{includeJs file="library/livecart.js"}

{includeCss file="base.css"}
{includeCss file="stat.css"}

{assign var="TITLE" value="Product Category Management"}

{include file="layout/header.tpl"}
	<div id="catgegoryContainer" style="float:left; width: 260px;">

		<div id="categoryBrowser" style="padding: 10px; border: 1px solid #ccc; background-color: #f1f1f1;">
			here goes category tree
			<li>Computers</li>
			<li>Books</li>
			<li>Hardware</li>
		</div>
	</div>
	
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