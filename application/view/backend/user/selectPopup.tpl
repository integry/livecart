{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree_start.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="backend/User.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeCss file="backend/User.css"}
{includeCss file="backend/SelectCustomerPopup.css"}

{include file="backend/eav/includes.tpl"}

{pageTitle help="userGroups"}{t _select_users}{/pageTitle}
{include file="layout/backend/meta.tpl"}

<div id="userGroupsWrapper" class="maxHeight h--50">
	<div id="userGroupsBrowserWithControlls" class="treeContainer maxHeight">
		<ul class="menu popup">
			<li class="done">
				<a href="#" onclick="window.close(); return false;">
					{t _cancel_adding}
				</a>
			</li>
		</ul>
		<div id="userGroupsBrowser" class="treeBrowser"></div>
	</div>

	<span id="fromUsersPage">
		{include file="backend/userGroup/groupContainer.tpl"}
	</span>
</div>

{literal}
<script type="text/javascript">
	window.ordersActiveGrid = {};

	var users = new Backend.UserGroup({/literal}{json array=$userGroups}{literal});
	window.usersActiveGrid = {};
</script>
{/literal}

</body>
</html>