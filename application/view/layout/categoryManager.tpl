{includeJs file="library/prototype/prototype.js"}
{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/dhtmlxtabbar/dhtmlXTabbar.js"}
{includeJs file="backend/categoryManager.js"}

{includeCss file="base.css"}
{includeCss file="stat.css"}
{includeCss file="form.css"}
<!--{includeCss file="backend/dhtmlxtabbar/dhtmlXTabbar.css"}-->
{includeCss file="tabControll.css"}
{includeCss file="backend/dhtmlxtree/dhtmlXTree.css"}

{assign var="TITLE" value="Product Category Management"}

{include file="layout/header.tpl"}

<script>
	var specFieldUrl = '{link controller=backend.specField action=index}';
</script>

	<div id="catgegoryContainer" style="float:left; width: 260px;">
		<div id="categoryBrowser" style="padding: 10px; border: 1px solid #ccc; background-color: #f1f1f1;">
		</div>
		<div>
			<a href="">Create a new category</a>
		</div>
	</div>
	
	<!--
	<div id="initialContent" style="display: none;">
		<div id="mainDetailsSection" style="padding: 20px">
		{* {$ACTION_VIEW} *}
		</div>
	</div>
	-->

	<div id="managerContainer" style="margin-left: 270px; height: 100%;">
		<div id="tabContainer">
			<ul>
				<li id="tabMainDetails" class="active">Main Details</li>
				<li id="tabFields" class="inactive" onclick="new LiveCart.AjaxUpdater('{link controller=backend.specField action=index}', 'sectionFields', 'tabFieldsIndicator');"> <img src="image/indicator.gif" id="tabFieldsIndicator" style="display: none;"/> Fields</li>
				<li class="inactive">Filters</li>
			</ul>
		</div>
		<div id="sectionContainer">
			<div id="sectionMainDetails">{$ACTION_VIEW}</div>
			<div id="sectionFields"></div>
		</div>
	</div>


<script type="text/javascript">
	LiveCart.CategoryManager.init();
</script>

<div id="specFieldSection"></div>

{include file="layout/footer.tpl"}