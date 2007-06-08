{* User groups container *}
<div id="userGroupsManagerContainer" class="treeManagerContainer maxHeight">   
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUsers" class="tab inactive">
				<a href="{link controller=backend.userGroup action=users}?id=_id_">{t _group_users}</a>
				<span class="tabHelp">userGroups.shippingRates</span>
			</li>
            
			<li id="tabUserGroup" class="tab active">
				<a href="{link controller=backend.userGroup action=edit}?id=_id_">{t _group_info}</a>
				<span class="tabHelp">userGroup.index</span>
			</li>
			
			<li id="tabRoles" class="tab inactive">
				<a href="{link controller=backend.roles action=index}?id=_id_">{t _group_permissions}</a>
				<span class="tabHelp">userGroups.shippingRates</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>