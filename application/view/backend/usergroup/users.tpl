<div>

<fieldset class="container" {denied role="user.create"}style="display: none;"{/denied}>
	<ul id="userGroup_[[userGroupID]]_addUser_menu" class="menu">
		<li class="addUser">
			<a id="userGroup_[[userGroupID]]_addUser" href="#addUser">{t _add_new_user}</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
		<li class="done addUserCancel">
			<a id="userGroup_[[userGroupID]]_addUserCancel" href="#cancelAddingUser" class="hidden">{t _cancel_adding_new_user} </a>
		</li>
	</ul>

	<div id="newUserForm_[[userGroupID]]" style="display: none;">
		<ul class="menu">
			<li class="done">
				<a class="cancel" href="#">{t _cancel_adding_new_user}</a>
			</li>
		</ul>
		<fieldset  class="addForm newUserForm">
			<legend>[[ capitalize({t _add_new_user}) ]]</legend>
			[[ partial('backend/user/info.tpl', ['someUser': newUser]) ]]
		</fieldset>
	</div>

	<script type="text/javascript">
		("fromUsersPage").appendChild(("newUserForm_[[userGroupID]]"))
	</script>


	<script type="text/javascript">
		Element.observe(("userGroup_[[userGroupID]]_addUser"), 'click', function(e)
		{
			e.preventDefault();
			Backend.User.Add.prototype.getInstance([[userGroupID]]).showAddForm([[userGroupID]]);
		});
	</script>

</fieldset>


<script type="text/javascript">
	Backend.UserGroup.GridFormatter.userUrl = '{backendUserUrl}';
</script>


{activeGrid
	prefix="users"
	id=userGroupID
	role="user.mass"
	controller="backend.userGroup" action="lists"
	displayedColumns=displayedColumns
	availableColumns=availableColumns
	totalCount=totalCount
	container="tabPageContainer"
	dataFormatter="Backend.UserGroup.GridFormatter"
	count="backend/userGroup/count.tpl"
	massAction="backend/userGroup/massAction.tpl"
	advancedSearch=true
}

</div>


<script type="text/javascript">
	var massHandler = new Backend.UserGroup.massActionHandler(('userMass_[[userGroupID]]'), window.activeGrids['users_[[userGroupID]]']);
	massHandler.deleteConfirmMessage = '[[ addslashes({t _are_you_sure_you_want_to_delete_this_user}) ]]' ;
	massHandler.nothingSelectedMessage = '[[ addslashes({t _nothing_selected}) ]]' ;

	usersActiveGrid[[[userGroupID]]] = window.activeGrids['users_[[userGroupID]]'];
</script>
