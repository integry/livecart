{includeJs file="library/livecart.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="backend/SelectFile.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/SelectFile.css"}

{% block title %}{t _select_file}{{% endblock %}

[[ partial("layout/backend/meta.tpl") ]]


<style>
	body
	{
		background-image: none;
	}
</style>


<div id="pageTitleContainer">
	<div id="pageTitle">[[PAGE_TITLE]]</div>
	<div id="breadcrumb_template" class="dom_template">
		<span class="breadcrumb_item"><a href=""></a></span>
		<span class="breadcrumb_separator"> &gt; </span>
		<span class="breadcrumb_lastItem"></span>
	</div>
	<div id="breadcrumb"></div>
</div>

<div id="popupCategoryContainer" class="treeContainer">

	<div id="categoryBrowser" class="treeBrowser"> </div>

</div>

<div id="fileContainer">

{activeGrid
	prefix="files"
	id=0
	controller="backend.selectFile"
	action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=0
	filters=$filters
	container="fileContainer"
	count="backend/selectFile/count.tpl"
}

</div>


<script type="text/javascript">
	var inst = window.selectFileInstance;
	inst.grid = window.activeGrids['files_0'];

	inst.grid.ricoGrid.metaData.options.largeBufferSize = 100;

	inst.links = {};
	inst.links.categoryRecursiveAutoloading = '[[ url("backend.selectFile/xmlRecursivePath") ]]';
	inst.links.categoryAutoloading = '[[ url("backend.selectFile/xmlBranch") ]]';

	inst.init();
	inst.addCategories({json array=$root});
	inst.treeBrowser.setXMLAutoLoading(inst.links.categoryAutoloading);
	inst.addCategories({json array=$directoryList});

	inst.initPage();

	inst.loadDirectory({json array=$current});
</script>


</body>
</html>