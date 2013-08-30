{* User groups container *}
<div id="userGroupsManagerContainer" class="treeManagerContainer maxHeight">
	<div id="loadingUser" style="display: none;">
		<span id="loadingUserMsg">{t _loading_user}<span class="progressIndicator"></span></span>
	</div>
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUsers" class="tab inactive">
				<a href="[[ url("backend.userGroup/users") ]]?id=_id_">{t _group_users}</a>
				<span class="tabHelp">users</span>
			</li>

			<li id="tabUserGroup" class="tab active">
				<a href="[[ url("backend.userGroup/edit") ]]?id=_id_">{t _group_info}</a>
				<span class="tabHelp">users</span>
			</li>

			<li id="tabRoles" class="tab inactive">
				<a href="[[ url("backend.roles/index") ]]?id=_id_">{t _group_permissions}</a>
				<span class="tabHelp">users</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>