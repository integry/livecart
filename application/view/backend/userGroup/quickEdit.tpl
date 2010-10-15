{form handle=$form
	action="controller=backend.userGroup action=saveQuickEdit id=`$someUser.ID`" id="userInfo_`$someUser.UserGroup.ID`_`$someUser.ID`_form"
	onsubmit="return false;" method="post"
	role="user.create(backend.userGroup/index),user.update(backend.user/info)"
}
	<fieldset class="quickEditOuterContainer">
		<div class="quickEditContainer1">
			<p class="required">
				<label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_userGroup" class="user_userGroupLabel">{t _user_group}</label>
				<fieldset class="error user_userGroup">
					{selectfield name="UserGroup" options=$availableUserGroups id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_userGroup"}
					<div class="errorText hidden"> </div>
				</fieldset>
			</p>
			<p class="required">
				<label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_firstName">{t _first_name}</label>
				<fieldset class="error">
					{textfield name="firstName" id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_firstName"}
					<div class="errorText" style="display: none" ></span>
				</fieldset>
			</p>
			<p class="required">
				<label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_lastName">{t _last_name}</label>
				<fieldset class="error">
					{textfield name="lastName" id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_lastName"}
					<div class="errorText" style="display: none" ></span>
				</fieldset>
			</p>
			<p class="required">
				<label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_email">{t _email}</label>
				<fieldset class="error">
					{textfield name="email" id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_email"}
					<div class="errorText" style="display: none" ></span>
				</fieldset>
			</p>
			<p {if !$someUser.ID}class="required"{/if}>
				<label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password">{t _password}</label>

				<a class="generatePassword" href="javascript:void(0);" onclick="Backend.UserQuickEdit.generatePassword(this, '{link controller=backend.user action=generatePassword}');" class="user_password_generate">{t _generate_password}</a>
				<fieldset class="error userPasswordBlock">
					<span class="progressIndicator generatePasswordProgressIndicator" style="display: none;"></span>
					{password name="password" id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_password" class="user_password text" style="width:182px;"}
					<fieldset class="error showPassword">
						<input onchange="Backend.UserQuickEdit.togglePassword(this);" type="checkbox" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox user_password_show"/>
						<label class="showPasswordLabel" for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox">{t _show_password}</label>
					</fieldset >
					<div class="errorText" style="display: none" ></span>
				</fieldset>
			</p>
	</div>
	<div class="quickEditContainer2">
		{if $lastOrder}
			{include file="backend/customerOrder/block/orderInfo.tpl" order=$lastOrder}
		{else}
			<h3>{t _user_has_not_made_any_orders_yet}</h3>
		{/if}
	</div>
	<div class="quickEditContainer3">
		{if $orders}
		<div class="quickEditScroller">
			<table class="qeOrders">
				<tbody>
					<tr>
						<td>{t _invoice_number}</td>
						<td>{t _date}</td>
						<td>{t _ammount}</td>
						<td>{t _status}</td>
					</tr>
					{foreach $orders as $order}
						<tr onclick="Backend.UserQuickEdit.showOrderDetails(this);">
							<td><a href="javascript:void(0);">{$order.invoiceNumber|escape}</a></td>
							<td title="{$order.formatted_dateCreated.date_medium|escape} {$order.formatted_dateCreated.time_short|escape}">{$order.formatted_dateCreated.date_short|escape}</td>
							<td>{$order.formatted_totalAmount|escape}</td>
							<td>{t `$order.status_name`}</td>
							<td class="hidden">{include file="backend/customerOrder/block/orderInfo.tpl" order=$order}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		{/if}
	</div>
	{include file="block/activeGrid/quickEditControls.tpl"}
	</fieldset>
{/form}