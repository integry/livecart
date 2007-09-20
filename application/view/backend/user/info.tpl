{form handle=$form action="controller=backend.user action=update" id="userInfo_`$someUser.UserGroup.ID`_`$someUser.ID`_form" onsubmit="Backend.User.Editor.prototype.getInstance(`$someUser.ID`, false).submitForm(); return false;" method="post" role="user.create(backend.userGroup/index),user.update(backend.user/info)"}
    <p>
        <fieldset class="error checkbox">
            {checkbox name="isEnabled"  id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_isEnabled" class="checkbox"}
            <label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_isEnabled" class="checkbox">{t _is_enabled}</label>
            <div class="errorText" style="display: none" ></span>
        </fieldset>
    </p>
    
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

    <p>
        <label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_companyName">{t _company_name}</label>
        <fieldset class="error">
            {textfield name="companyName" id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_companyName"}
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
            <a href="#generate" class="user_password_generate">{t _generate_password}</a>
            <fieldset class="error showPassword">
                <input type="checkbox" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show" class="checkbox user_password_show"/>
                <label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_password_show">{t _show_password}</label>
            </fieldset >
            <div class="errorText" style="display: none" ></span>
    	</fieldset>
    </p>
        
    <p class="sameAddress">
        {checkbox name="sameAddresses"  id="user_`$someUser.UserGroup.ID`_`$someUser.ID`_sameAddresses" class="checkbox"}
        <label for="user_{$someUser.UserGroup.ID}_{$someUser.ID}_sameAddresses">{t _same_billing_and_shipping_addresses?}</label>       
    </p>
    
    <br class="clear" />
    
    <fieldset id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_billingAddress" class="user_billingAddress">
        <legend>{t _billing_address}</legend>
        {include file=backend/user/address_edit.tpl namePrefix="billingAddress_" idPrefix="user_`$someUser.UserGroup.ID`_`$someUser.ID`_billingAddress" address=$someUser.defaultBillingAddress states=$billingAddressStates}
    </fieldset>

    <fieldset id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_shippingAddress" class="user_shippingAddress">
        <legend>{t _shipping_address}</legend>
        {include file=backend/user/address_edit.tpl namePrefix="shippingAddress_" idPrefix="user_`$someUser.UserGroup.ID`_`$someUser.ID`_shippingAddress" address=$someUser.defaultShippingAddress states=$shippingAddressStates}
    </fieldset>
    
    
    <fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
    	<input type="submit" name="save" class="submit" value="Save" id="user_{$someUser.UserGroup.ID}_{$someUser.ID}_submit"> 
        {t _or} 
        <a class="cancel" href="#">{t _cancel}</a>
    </fieldset>
    
    
    <script type="text/javascript">
        {literal}
        try
        {
            if({/literal}{$someUser.ID}{literal} > 0)
            {
                console.info('edit');
                Backend.User.Editor.prototype.getInstance({/literal}{$someUser.ID}{literal});
            }
            else
            {
                console.info('add');
                Backend.User.Add.prototype.getInstance({/literal}{$someUser.UserGroup.ID}{literal});
            }
        }
        catch(e)
        {
            console.info(e);
        }
        {/literal}
    </script>
{/form}