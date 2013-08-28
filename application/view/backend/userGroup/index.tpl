{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree_start.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}

{includeCss file="backend/CustomerOrder.css"}
{includeJs file="backend/CustomerOrder.js"}

{includeJs file="backend/User.js"}
{includeCss file="backend/User.css"}

{includeJs file="backend/Roles.js"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveGrid.css"}

{include file="backend/eav/includes.tpl"}

{pageTitle help="userGroups"}{t _livecart_users}{/pageTitle}
{include file="layout/backend/header.tpl"}

<script type="text/javascript">
	Backend.UserGroup.userGroups = [[userGroups]];
</script>

<div id="userGroupsWrapper" class="maxHeight h--50">
	<div id="userGroupsBrowserWithControlls" class="treeContainer maxHeight">
		<div id="userGroupsBrowser" class="treeBrowser"></div>
		<ul id="userGroupsBrowserControls" class="verticalMenu">
			<li class="addTreeNode" {denied role="userGroup.create"}style="display: none;"{/denied}><a id="userGroups_add" href="#add">{t _add_group}</a></li>
			<li class="removeTreeNode" {denied role="userGroup.remove"}style="display: none;"{/denied}><a id="userGroups_delete" href="#delete">{t _delete_group}</a></li>
		</ul>
	</div>

	<span id="fromUsersPage">
		{include file="backend/customerOrder/orderContainer.tpl"}
		{include file="backend/userGroup/groupContainer.tpl"}
		{include file="backend/userGroup/userContainer.tpl"}
	</span>

{literal}
<script type="text/javascript">
	window.ordersActiveGrid = {};
	Backend.showContainer("userGroupsManagerContainer");
	{/literal}
		{allowed role="order"}
			Backend.CustomerOrder.prototype.ordersMiscPermission = true;
		{/allowed}
	{literal}

	Backend.UserGroup.prototype.Messages.confirmUserDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_user}{literal}';
	Backend.UserGroup.prototype.Messages.confirmUserGroupRemove = '{/literal}{t _are_you_sure_you_want_to_delete_this_user_group}{literal}';
	Backend.UserGroup.prototype.Messages.defaultUserName = '{/literal}{t _default_user}{literal}';
	Backend.UserGroup.prototype.Messages.youCanntoDeleteThisGroup = '{/literal}{t _you_cannot_delete_this_group}{literal}';
	Backend.User.Group.prototype.Messages.savedMessage = '{/literal}{t _form_has_been_successfully_saved}{literal}';

	Backend.CustomerOrder.Links.selectCustomer = '{/literal}{link controller="backend.customerOrder" action=selectCustomer}{literal}';
	Backend.CustomerOrder.Editor.prototype.Messages.orderNum = '{/literal}{t _order_number|escape}{literal}';
	Backend.CustomerOrder.Messages.selecCustomerTitle = '{/literal}{t _select_customer_title}{literal}';
	Backend.CustomerOrder.Links.createOrder = '{/literal}{link controller="backend.customerOrder" action=create}{literal}';

	Backend.User.Group.prototype.Links.save = '{/literal}{link controller="backend.userGroup" action=save}{literal}';
	Backend.User.Group.prototype.Links.remove = '{/literal}{link controller="backend.userGroup" action=delete}{literal}';
	Backend.User.Group.prototype.Links.createNewUserGroup = '{/literal}{link controller="backend.userGroup" action=create}{literal}';
	Backend.User.Group.prototype.Links.removeNewGroup = '{/literal}{link controller="backend.userGroup" action=remove}{literal}';
	Backend.User.Group.prototype.Links.create = '{/literal}{link controller="backend.userGroup" action=create}{literal}';

	Backend.User.Editor.prototype.Links.create = '{/literal}{link controller="backend.user" action=create}{literal}';
	Backend.User.Editor.prototype.Links.update = '{/literal}{link controller="backend.user" action=update}{literal}';
	Backend.User.Editor.prototype.Links.generatePassword = '{/literal}{link controller="backend.user" action=generatePassword}{literal}';

	Backend.UserGroup.prototype.usersMiscPermision = {/literal}{allowed role="user"}true{/allowed}{denied role="user"}false{/denied}{literal};

	var users = new Backend.UserGroup({/literal}{json array=$userGroups}{literal});
	window.ordersActiveGrid = {};
	window.usersActiveGrid = {};
</script>
{/literal}


{include file="layout/backend/footer.tpl"}
