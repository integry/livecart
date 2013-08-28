{includeJs file="library/livecart.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/Product.js"}

{includeCss file="library/ActiveGrid.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/Product.css"}
{includeCss file="backend/ProductRelatedSelectProduct.css"}

[[ partial("backend/category/loadJsTree.tpl") ]]

{% block title %}{t _select_product}{{% endblock %}

[[ partial("layout/backend/meta.tpl") ]]

<a id="help" href="#" target="_blank" style="display: none;">Help</a>

<div id="catgegoryContainer" class="treeContainer">

	<div style="margin-bottom: 5px; text-align: center;">
		<ul class="menu popup">
			<li class="done">
				<a class="menu" href="#" onclick="window.close(); return false;">
					{t _done_adding}
				</a>
			</li>
		</ul>
	</div>

	<div id="categoryBrowser" class="treeBrowser"> </div>

</div>
<div id="activeCategoryPath"></div>

<div id="managerContainer" class="treeManagerContainer popup">
	<div id="tabContainer" class="tabContainer">
	<ul id="tabList" class="tabList tabs">
		<li id="tabProducts" class="tab active">
			<a href="{link controller="backend.product" action=index id=_id_}">{t _products}</a>
			<span> </span>
			<span class="tabHelp">products</span>
		</li>
	</ul>
	</div>
	<div id="sectionContainer" class="sectionContainer maxHeight  h--50"> </div>
</div>

{literal}
<script type="text/javascript">
	Backend.Category.links = {};
	Backend.Category.links.countTabsItems = '{/literal}{link controller="backend.category" action=countTabsItems id=_id_}{literal}';

	Backend.Category.init({/literal}{json array=$categoryList}{literal});

	{/literal}
		{allowed role="product"}
			Backend.Product.productsMiscPermision = true;
		{/allowed}
	{literal}
</script>
{/literal}

</body>
</html>