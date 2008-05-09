{* User managing container *}
<div id="userManagerContainer" class="treeManagerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a href="#cancelEditing" id="cancel_user_edit" class="cancel">{t _cancel_editing_user_info}</a></li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabUserInfo" class="tab active">
				<a href="{link controller=backend.user action=info id=_id_}"}">{t _user_info}</a>
				<span class="tabHelp">users.edit</span>
			</li>
			<li id="tabOrdersList" class="tab active">
				<a href="{link controller=backend.customerOrder action=orders id=1 query='userID=_id_'}">{t _orders}</a>
				<span class="tabHelp">customerOrders.orders</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>

	{literal}
	<script type="text/javascript">
		Event.observe($("cancel_user_edit"), "click", function(e) {
			Event.stop(e);
			var user = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.getCurrentId(), false);
			user.cancelForm();
		});
	</script>
	{/literal}
</div>