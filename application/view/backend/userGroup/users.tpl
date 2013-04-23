<div>

<fieldset class="container" {denied role="user.create"}style="display: none;"{/denied}>
	<ul id="userGroup_{$userGroupID}_addUser_menu" class="menu">
		<li class="addUser">
			<a id="userGroup_{$userGroupID}_addUser" href="#addUser">{t _add_new_user}</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
		<li class="done addUserCancel">
			<a id="userGroup_{$userGroupID}_addUserCancel" href="#cancelAddingUser" class="hidden">{t _cancel_adding_new_user} </a>
		</li>
	</ul>

	<div id="newUserForm_{$userGroupID}" style="display: none;">
		<ul class="menu">
			<li class="done">
				<a class="cancel" href="#">{t _cancel_adding_new_user}</a>
			</li>
		</ul>
		<fieldset  class="addForm newUserForm">
			<legend>{t _add_new_user|capitalize}</legend>
			{include file="backend/user/info.tpl" someUser=$newUser}
		</fieldset>
	</div>

	<script type="text/javascript">
		$("fromUsersPage").appendChild($("newUserForm_{$userGroupID}"))
	</script>

	{literal}
	<script type="text/javascript">
		Element.observe($("{/literal}userGroup_{$userGroupID}_addUser{literal}"), 'click', function(e)
		{
			e.preventDefault();
			Backend.User.Add.prototype.getInstance({/literal}{$userGroupID}{literal}).showAddForm({/literal}{$userGroupID}{literal});
		});
	</script>
	{/literal}
</fieldset>

{literal}
<script type="text/javascript">
	Backend.UserGroup.GridFormatter.userUrl = '{/literal}{backendUserUrl}{literal}';
</script>
{/literal}

{activeGrid
	prefix="users"
	id=$userGroupID
	role="user.mass"
	controller="backend.userGroup" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	container="tabPageContainer"
	dataFormatter="Backend.UserGroup.GridFormatter"
	count="backend/userGroup/count.tpl"
	massAction="backend/userGroup/massAction.tpl"
	advancedSearch=true
}

</div>

{literal}
<script type="text/javascript">
	var massHandler = new Backend.UserGroup.massActionHandler($('{/literal}userMass_{$userGroupID}{literal}'), window.activeGrids['{/literal}users_{$userGroupID}{literal}']);
	massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_this_user|addslashes}{literal}' ;
	massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;

	usersActiveGrid[{/literal}{$userGroupID}{literal}] = window.activeGrids['{/literal}users_{$userGroupID}{literal}'];
</script>
{/literal}