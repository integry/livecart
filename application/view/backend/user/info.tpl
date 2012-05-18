{form handle=$form action="controller=backend.user action=update" id="userInfo_`$someUser.UserGroup.ID`_`$someUser.ID`_form" onsubmit="Backend.User.Editor.prototype.getInstance(`$someUser.ID`, false).submitForm(); return false;" method="post" role="user.create(backend.userGroup/index),user.update(backend.user/info)"}
	{input name="isEnabled"}
		{checkbox}
		{label}{t _is_enabled}:{/label}
	{/input}

	{input name="UserGroup"}
		{label}{t _user_group}:{/label}
		{selectfield options=$availableUserGroups}
	{/input}

	{input name="firstName"}
		{label}{t _first_name}:{/label}
		{textfield}
	{/input}

	{input name="lastName"}
		{label}{t _last_name}:{/label}
		{textfield}
	{/input}

	{input name="companyName"}
		{label}{t _company_name}:{/label}
		{textfield}
	{/input}

	{input name="email"}
		{label}{t _email}:{/label}
		{textfield}
	{/input}

	{include file="backend/eav/fields.tpl" item=$someUser}

	{input name="password"}
		{label}{t _password}:{/label}
		<span class="progressIndicator generatePasswordProgressIndicator" style="display: none;"></span>
		{password id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_password" class="user_password"}
		<a href="#generate" class="user_password_generate">{t _generate_password}</a>
		<fieldset class="error showPassword">
			<input type="checkbox" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox user_password_show"/>
			<label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox">{t _show_password}</label>
		</fieldset>
	{/input}

	{input name="sameAddress"}
		{checkbox}
		{label}{t _same_billing_and_shipping_addresses?}:{/label}
	{/input}

	<br class="clear" />

	<fieldset id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_billingAddress" class="user_billingAddress">
		<legend>{t _billing_address}</legend>
		{include file="backend/user/address_edit.tpl" namePrefix="billingAddress_" eavPrefix="billingAddress_" idPrefix="user_`$someUser.UserGroup.ID`_`$someUser.ID`_billingAddress" address=$someUser.defaultBillingAddress states=$billingAddressStates}
	</fieldset>

	<fieldset id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_shippingAddress" class="user_shippingAddress">
		<legend>{t _shipping_address}</legend>
		{include file="backend/user/address_edit.tpl" namePrefix="shippingAddress_" eavPrefix="shippingAddress_" idPrefix="user_`$someUser.UserGroup.ID`_`$someUser.ID`_shippingAddress" address=$someUser.defaultShippingAddress states=$shippingAddressStates}
	</fieldset>

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _save}" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_submit">
		{t _or}
		<a class="cancel" href="#">{t _cancel}</a>
	</fieldset>

	<script type="text/javascript">
		{literal}
		if({/literal}{$someUser.ID}{literal} > 0)
		{
			Backend.UserGroup.prototype.treeBrowser.selectItem({/literal}{$someUser.UserGroup.ID|default:-1}{literal}, false);
			Backend.User.Editor.prototype.getInstance({/literal}{$someUser.ID}{literal});
		}
		else
		{
//				Backend.User.Add.prototype.getInstance({/literal}{$someUser.UserGroup.ID}{literal});
		}
		{/literal}
	</script>
{/form}