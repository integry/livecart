{* User groups container *}
<div id="orderGroupsManagerContainer" class="managerContainer">   
	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserGroup" class="tab active">
				<a href="{link controller=backend.orderGroup action=edit}?id=_id_">{t _user_group}</a>
				<span class="tabHelp">orderGroup.index</span>
			</li>
			
			<li id="tabUsers" class="tab inactive">
				<a href="{link controller=backend.orderGroup action=users}?id=_id_">{t _group_users}</a>
				<span class="tabHelp">orderGroups.shippingRates</span>
			</li>
			
			<li id="tabRoles" class="tab inactive">
				<a href="{link controller=backend.roles action=index}?id=_id_">{t _group_permissions}</a>
				<span class="tabHelp">orderGroups.shippingRates</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>