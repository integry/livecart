<div id="userGroup_{$userGroup.ID}"></div>

{form handle=$userGroupForm action="controller=backend.userGroup action=save" id="userGroupForm_`$userGroup.ID`" method="post" onsubmit="Backend.User.Group.prototype.getInstance(this).save(); return false;"}
	{hidden name="ID"}
    
    <fieldset class="error">
        <label>{t _name}</label>
        {textfield name="name"}
        <span class="errorText" style="display: none" ></span>
	</fieldset>
    
    <fieldset class="error">
        <label>{t _description}</label>
        {textarea name="description"}
	</fieldset>
    
    <fieldset class="userGroup_controls">
        <span class="activeForm_progress"></span>
        <input type="submit" class="userGroup_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="userGroup_cancel cancel">{t _cancel}</a>
    </fieldset>
{/form}
<script type="text/javascript">
    Backend.User.Group.prototype.getInstance("userGroupForm_{$userGroup.ID}");
</script>