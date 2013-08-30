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

[[ partial("backend/eav/includes.tpl") ]]

{pageTitle help="userGroups"}{t _livecart_users}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

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
		[[ partial("backend/customerOrder/orderContainer.tpl") ]]
		[[ partial("backend/userGroup/groupContainer.tpl") ]]
		[[ partial("backend/userGroup/userContainer.tpl") ]]
	</span>


<script type="text/javascript">
	window.ordersActiveGrid = {};
	Backend.showContainer("userGroupsManagerContainer");

		{allowed role="order"}
			Backend.CustomerOrder.prototype.ordersMiscPermission = true;
		{/allowed}


	Backend.UserGroup.prototype.Messages.confirmUserDelete = '{t _are_you_sure_you_want_to_delete_this_user}';
	Backend.UserGroup.prototype.Messages.confirmUserGroupRemove = '{t _are_you_sure_you_want_to_delete_this_user_group}';
	Backend.UserGroup.prototype.Messages.defaultUserName = '{t _default_user}';
	Backend.UserGroup.prototype.Messages.youCanntoDeleteThisGroup = '{t _you_cannot_delete_this_group}';
	Backend.User.Group.prototype.Messages.savedMessage = '{t _form_has_been_successfully_saved}';

	Backend.CustomerOrder.Links.selectCustomer = '{link controller="backend.customerOrder" action=selectCustomer}';
	Backend.CustomerOrder.Editor.prototype.Messages.orderNum = '[[ escape({t _order_number}) ]]';
	Backend.CustomerOrder.Messages.selecCustomerTitle = '{t _select_customer_title}';
	Backend.CustomerOrder.Links.createOrder = '{link controller="backend.customerOrder" action=create}';

	Backend.User.Group.prototype.Links.save = '{link controller="backend.userGroup" action=save}';
	Backend.User.Group.prototype.Links.remove = '{link controller="backend.userGroup" action=delete}';
	Backend.User.Group.prototype.Links.createNewUserGroup = '{link controller="backend.userGroup" action=create}';
	Backend.User.Group.prototype.Links.removeNewGroup = '{link controller="backend.userGroup" action=remove}';
	Backend.User.Group.prototype.Links.create = '{link controller="backend.userGroup" action=create}';

	Backend.User.Editor.prototype.Links.create = '{link controller="backend.user" action=create}';
	Backend.User.Editor.prototype.Links.update = '{link controller="backend.user" action=update}';
	Backend.User.Editor.prototype.Links.generatePassword = '{link controller="backend.user" action=generatePassword}';

	Backend.UserGroup.prototype.usersMiscPermision = {allowed role="user"}true{/allowed}{denied role="user"}false{/denied};

	var users = new Backend.UserGroup({json array=$userGroups});
	window.ordersActiveGrid = {};
	window.usersActiveGrid = {};
</script>



[[ partial("layout/backend/footer.tpl") ]]
