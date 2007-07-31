<div>

{if $userGroupID >= -1}
    <fieldset class="container" {denied role="user.create"}style="display: none;"{/denied}>
    	<ul id="userGroup_{$userGroupID}_addUser_menu" class="menu">
    		<li>
    			<a id="userGroup_{$userGroupID}_addUser" href="#addUser">{t _add_new_user}</a>
    			<span class="progressIndicator" style="display: none;"></span>
    		</li>
    		<li>
    			<a id="userGroup_{$userGroupID}_addUserCancel" href="#cancelAddingUser" class="hidden">{t _cancel_adding_new_user} </a>
    		</li>
    	</ul>  
        
        <fieldset id="newUserForm_{$userGroupID}" style="display: none;" class="treeManagerContainer newUserForm">
            {include file="backend/user/info.tpl"}
        </fieldset>
        
        <script type="text/javascript">
            $("fromUsersPage").appendChild($("newUserForm_{$userGroupID}"))
        </script>
        
        {literal}
        <script type="text/javascript">
            try
            {
                Element.observe($("{/literal}userGroup_{$userGroupID}_addUser{literal}"), 'click', function(e)
                {
                    Event.stop(e);
                    Backend.User.Add.prototype.getInstance({/literal}{$userGroupID}{literal}).showAddForm({/literal}{$userGroupID}{literal}); 
                });
            }
            catch(e)
            {
                console.info(e);
            }
        </script>
        {/literal}
    </fieldset>
{/if}

<fieldset class="container">
                
    <span class="activeGridMass" {denied role="user.mass"}style="visibility: hidden;"{/denied} id="userMass_{$userGroupID}" >

	    {form action="controller=backend.user action=processMass id=$userGroupID" handle=$massForm onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        {t _with_selected}:
        <select name="act" class="select">
            <option value="enable_isEnabled">Enable</option>
            <option value="disable_isEnabled">Disable</option>
            <option value="delete">Delete</option>
        </select>
        
        <span class="bulkValues" style="display: none;">

        </span>
        
        <input type="submit" value="{tn _process}" class="submit" />
        <span class="progressIndicator" style="display: none;"></span>
        
        {/form}
        
    </span>
    
    <span class="activeGridItemsCount">
		<span id="userCount_{$userGroupID}">
			<span class="rangeCount">Listing users %from - %to of %count</span>
			<span class="notFound">No users found</span>
		</span>    
		<br />
		<div>
			<a href="#" onclick="Element.show($('userColumnMenu_{$userGroupID}')); return false;">{t Columns}</a>
		</div>
		<div id="userColumnMenu_{$userGroupID}" class="activeGridColumnsRoot" style="display: none;">
  		  <form action="{link controller=backend.userGroup action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.parentNode.parentNode.parentNode.parentNode.parentNode, document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="POST">
			
			<input type="hidden" name="group" value="{$userGroupID}" />
			
			<div class="activeGridColumnsSelect">
				<div class="activeGridColumnsSelectControls">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" name="sm" value="{tn Change columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('userColumnMenu_{$userGroupID}')); return false;" href="#cancel">{t _cancel}</a>
				</div>
			    <div class="activeGridColumnsList error">
					{foreach from=$availableColumns item=item key=column}
					<p>
						<input type="checkbox" name="col[{$column}]" class="checkbox" id="column_{$column}"{if $displayedColumns.$column}checked="checked"{/if} />
						<label for="column_{$column}" class="checkbox">
							{$item.name}
						</label>
					</p>
					{/foreach}
				</div>
			</div>
		  </form>
		</div>
	</span>
    
</fieldset>

{activeGrid prefix="users" id=$userGroupID role="user.mass" controller="backend.userGroup" action="lists" displayedColumns=$displayedColumns availableColumns=$availableColumns totalCount=$totalCount}

</div>

{literal}
<script type="text/javascript">

	grid.setDataFormatter(Backend.UserGroup.GridFormatter);
    
    var massHandler = new Backend.UserGroup.massActionHandler($('{/literal}userMass_{$userGroupID}{literal}'), grid);
    massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_this_user|addslashes}{literal}' ;
    
    usersActiveGrid[{/literal}{$userGroupID}{literal}] = grid;

</script>
{/literal}