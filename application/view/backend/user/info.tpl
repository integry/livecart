{form handle=$form action="controller=backend.user action=saveInfo" id="userInfo_`$user.ID`_form" onsubmit="Backend.User.Editor.prototype.getInstance(`$user.ID`, false).submitForm(); return false;" method="post"}

   	<div class="userInfoSaveConf" style="display: none;">
   		<div class="yellowMessage">
   			<div>
   				{t _user_information_was_saved_successfuly}
   			</div>
   		</div>
   	</div>
    
    
    <label for="user_{$user.ID}_email">{t _email}</label>
    <fieldset class="error">
        {textfield name="email" id="user_`$user.ID`_email"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    
    <label for="user_{$user.ID}_password1">{t _password}</label>
    <fieldset class="error">
        {password name="password1" id="user_`$user.ID`_password1"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    <label for="user_{$user.ID}_password2">{t _repeat_password}</label>
    <fieldset class="error">
        {password name="password2" id="user_`$user.ID`_password2"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    
    <label for="user_{$user.ID}_userGroup">{t _user_group}</label>
    <fieldset class="error">
		{selectfield name="UserGroup" options=$availableUserGroups id="user_`$user.ID`_userGroup"}
		<div class="errorText hidden"> </div>
    </fieldset> 


    <label for="user_{$user.ID}_firstName">{t _first_name}</label>
    <fieldset class="error">
        {textfield name="firstName" id="user_`$user.ID`_firstName"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    
    <label for="user_{$user.ID}_lastName">{t _last_name}</label>
    <fieldset class="error">
        {textfield name="lastName" id="user_`$user.ID`_lastName"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>

    <label for="user_{$user.ID}_companyName">{t _company_name}</label>
    <fieldset class="error">
        {textfield name="companyName" id="user_`$user.ID`_companyName"}
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    

    <fieldset class="error checkbox">
        {checkbox name="isEnabled"  id="user_`$user.ID`_isEnabled" class="checkbox"}
        <label for="user_{$user.ID}_isEnabled" class="checkbox">{t _is_enabled}</label>
        <div class="errorText" style="display: none" ></span>
	</fieldset>
    

    
    
    <fieldset>
    	<input type="submit" name="save" class="submit" value="Save"> {t _or} <a class="cancel" href="#">{t _cancel}</a>
    </fieldset>
    
    
    <script type="text/javascript">
        {literal}
        try
        {
            Backend.User.Editor.prototype.getInstance({/literal}{$user.ID}{literal});
        }
        catch(e)
        {
            console.info(e);
        }
        {/literal}
    </script>
{/form}