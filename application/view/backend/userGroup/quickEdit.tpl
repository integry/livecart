<div class="quickEditContainer1">
	{form handle=$form
	action="controller=backend.userGroup action=saveQuickEdit id=`$someUser.ID`" id="userInfo_`$someUser.UserGroup.ID`_`$someUser.ID`_form" 
		onsubmit="return false;" method="post"
		role="user.create(backend.userGroup/index),user.update(backend.user/info)"
	}
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
			<fieldset class="error userPasswordBlock">
				<span class="progressIndicator generatePasswordProgressIndicator" style="display: none;"></span>
				{password name="password" id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_password" class="user_password"}
				<a href="javascript:void(0);" onclick="Backend.UserQuickEdit.generatePassword(this, '{link controller=backend.user action=generatePassword}');" class="user_password_generate">{t _generate_password}</a>
				<fieldset class="error showPassword">
					<input onchange="Backend.UserQuickEdit.togglePassword(this);" type="checkbox" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox user_password_show"/>
					<label class="showPasswordLabel" for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox">{t _show_password}</label>
				</fieldset >
				<div class="errorText" style="display: none" ></span>
			</fieldset>
		</p>
		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="save" class="submit" value="{t _save}" onclick="return ActiveGrid.QuickEdit.onSubmit(this);" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_submit">
			{t _or}
			<a class="cancel" href="javascript:void(0);" onclick="return ActiveGrid.QuickEdit.onCancel(this);">{t _cancel}</a>
		</fieldset>
	{/form}
</div>

<div class="quickEditContainer2">
	{if $lastOrder}
		{include file="backend/userGroup/quickEdit_orderInfo.tpl" order=$lastOrder}
	{else}
		<h3>{t _user_has_not_made_any_orders_yet}</h3>
	{/if}
</div>
<div class="quickEditContainer3">
	{if $orders}
	<div style="overflow:auto; height:inherit">
		<table>
			<tbody>
				<tr>
					<td>{t _invoice_number}</td>
					<td>{t _date}</td>
					<td>{t _ammount}</td>
					<td>{t _status}</td>
				</tr>
				{foreach $orders as $order}
					<tr onclick="Backend.UserQuickEdit.showOrderDetails(this);">
						<td>{$order.invoiceNumber|escape}</td>
						<td>{$order.formatted_dateCreated.date_medium|escape}</td>
						<td>{$order.formatted_totalAmount|escape}</td>
						<td>{$order.status}</td>
						<td class="hidden">{include file="backend/userGroup/quickEdit_orderInfo.tpl" order=$order}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{else}

	{/if}
</div>
