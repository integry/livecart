<div>

<fieldset class="container" {denied role="user.create"}style="display: none;"{/denied}>
	<ul id="userGroup_{$userGroupID}_addUser_menu" class="menu">
		<li class="addUser">
			<a id="userGroup_{$userGroupID}_addUser" href="#addUser">{t _add_new_user}</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
		<li class="done addUserCancel">
			<a id="userGroup_{$userGroupID}_addUserCancel" href="#cancelAddingUser" class="hidden">{t _cancel_adding_new_user} </a>
		</li>
	</ul>  
    
    <div id="newUserForm_{$userGroupID}" style="display: none;">
    	<ul class="menu" style="margin-left: 270px;">
            <li class="done">   
                <a class="cancel" href="#">{t _cancel_adding_new_user}</a>
            </li>
        </ul>
        <fieldset  class="addForm treeManagerContainer newUserForm">
            <legend>{t _add_new_user|capitalize}</legend>
            {include file="backend/user/info.tpl" someUser=$newUser}
        </fieldset>
    </div>
    
    <script type="text/javascript">
        $("fromUsersPage").appendChild($("newUserForm_{$userGroupID}"))
    </script>
    
    {literal}
    <script type="text/javascript">
        Element.observe($("{/literal}userGroup_{$userGroupID}_addUser{literal}"), 'click', function(e)
        {
            Event.stop(e);
            Backend.User.Add.prototype.getInstance({/literal}{$userGroupID}{literal}).showAddForm({/literal}{$userGroupID}{literal}); 
        });
    </script>
    {/literal}
</fieldset>

<fieldset class="container activeGridControls">
                
    <span class="activeGridMass" {denied role="user.mass"}style="visibility: hidden;"{/denied} id="userMass_{$userGroupID}" >

	    {form action="controller=backend.user action=processMass id=$userGroupID" handle=$massForm onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        {t _with_selected}:
        <select name="act" class="select">
            <option value="enable_isEnabled">{t _enable}</option>
            <option value="disable_isEnabled">{t _disable}</option>
            <option value="delete">{t _delete}</option>
        </select>
        
        <span class="bulkValues" style="display: none;">

        </span>
        
        <input type="submit" value="{tn _process}" class="submit" />
        <span class="progressIndicator" style="display: none;"></span>
        
        {/form}
        
    </span>
    
    <span class="activeGridItemsCount">
		<span id="userCount_{$userGroupID}">
			<span class="rangeCount">{t _listing_users}</span>
			<span class="notFound" style="display: none;">{t _no_users}</span>
		</span>    
	</span>
    
</fieldset>

{activeGrid 
	prefix="users" 
	id=$userGroupID 
	role="user.mass" 
	controller="backend.userGroup" action="lists" 
	displayedColumns=$displayedColumns 
	availableColumns=$availableColumns 
	totalCount=$totalCount
	container="tabPageContainer"
}

</div>

{literal}
<script type="text/javascript">
    Backend.UserGroup.GridFormatter.userUrl = '{/literal}{backendUserUrl}{literal}';
	window.activeGrids['{/literal}users_{$userGroupID}{literal}'].setDataFormatter(Backend.UserGroup.GridFormatter);
    
    var massHandler = new Backend.UserGroup.massActionHandler($('{/literal}userMass_{$userGroupID}{literal}'), window.activeGrids['{/literal}users_{$userGroupID}{literal}']);
    massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_this_user|addslashes}{literal}' ;
    massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;    
    
    usersActiveGrid[{/literal}{$userGroupID}{literal}] = window.activeGrids['{/literal}users_{$userGroupID}{literal}'];
</script>
{/literal}