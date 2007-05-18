<div class="yellowMessage" style="display: none;">
   	<div>
   		{t _user_group_roles_where_successfully_updated}
   	</div>
</div>


{form id="roles_form_`$userGroup.ID`" handle=$form action="controller=backend.roles action=save id=`$userGroup.ID`" onsubmit="Backend.Roles.prototype.getInstance('roles_form_`$userGroup.ID`').save(event);" method="post"}
    
    <div id="userGroupsRolesTree" class="treeBrowser" ></div>
    
    <fieldset class="roles_controls">
        <span class="activeForm_progress"></span>
        <input type="submit" class="roles_save button submit" value="{t _save}" />
        {t _or}
        <a href="#cancel" class="roles_cancel cancel">{t _cancel}</a>
    </fieldset>	
{/form}

<script type="text/javascript">
    var roles = Backend.Roles.prototype.getInstance('roles_form_{$userGroup.ID}', {json array=$roles}, {json array=$activeRolesIDs});
</script>