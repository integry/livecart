{form handle=form action="backend.user/update" id="userInfo_`someUser.UserGroup.ID`_`someUser.ID`_form" onsubmit="Backend.User.Editor.prototype.getInstance(`someUser.ID`, false).submitForm(); return false;" method="post" role="user.create(backend.userGroup/index),user.update(backend.user/info)"}
	[[ checkbox('isEnabled', '_is_enabled') ]]

	[[ selectfld('UserGroup', '_user_group', availableUserGroups) ]]

	[[ textfld('firstName', '_first_name') ]]

	[[ textfld('lastName', '_last_name') ]]

	[[ textfld('companyName', '_company_name') ]]

	[[ textfld('email', '_email') ]]

	[[ partial('backend/eav/fields.tpl', ['item': someUser]) ]]

	{input name="password"}
		{label}{t _password}:{/label}
		<span class="progressIndicator generatePasswordProgressIndicator" style="display: none;"></span>
		{password id="user_`someUser.UserGroup.ID`_`someUser.ID`_password" class="user_password"}
		<a href="#generate" class="user_password_generate">{t _generate_password}</a>
		<fieldset class="error showPassword">
			<input type="checkbox" id="user_[[someUser.UserGroup.ID]]_[[someUser.ID]]_password_show" class="checkbox user_password_show"/>
			<label for="user_[[someUser.UserGroup.ID]]_[[someUser.ID]]_password_show" class="checkbox">{t _show_password}</label>
		</fieldset>
	{/input}

	{input name="sameAddress"}
		{checkbox id="user_`someUser.UserGroup.ID`_`someUser.ID`_sameAddresses"}
		{label}{t _same_billing_and_shipping_addresses?}:{/label}
	{/input}

	<br class="clear" />

	<fieldset id="user_[[someUser.UserGroup.ID]]_[[someUser.ID]]_billingAddress" class="user_billingAddress">
		<legend>{t _billing_address}</legend>
		[[ partial('backend/user/address_edit.tpl', ['namePrefix': "billingAddress_", 'eavPrefix': "billingAddress_", 'idPrefix': "user_`someUser.UserGroup.ID`_`someUser.ID`_billingAddress", 'address': someUser.defaultBillingAddress, 'states': billingAddressStates]) ]]
	</fieldset>

	<fieldset id="user_[[someUser.UserGroup.ID]]_[[someUser.ID]]_shippingAddress" class="user_shippingAddress">
		<legend>{t _shipping_address}</legend>
		[[ partial('backend/user/address_edit.tpl', ['namePrefix': "shippingAddress_", 'eavPrefix': "shippingAddress_", 'idPrefix': "user_`someUser.UserGroup.ID`_`someUser.ID`_shippingAddress", 'address': someUser.defaultShippingAddress, 'states': shippingAddressStates]) ]]
	</fieldset>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _save}" id="user_[[someUser.UserGroup.ID]]_[[someUser.ID]]_submit">
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>

	<script type="text/javascript">

		if([[someUser.ID]] > 0)
		{
			Backend.UserGroup.prototype.treeBrowser.selectItem({someUser.UserGroup.ID|default:-1}, false);
			Backend.User.Editor.prototype.getInstance([[someUser.ID]]);
		}
		else
		{
//				Backend.User.Add.prototype.getInstance([[someUser.UserGroup.ID]]);
		}

	</script>
{/form}