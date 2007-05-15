<div>

{if $userGroupID >= -1}
    <fieldset class="container">
    	<ul id="userGroup_{$userGroupID}_addUser_menu" class="menu">
    		<li>
    			<a id="userGroup_{$userGroupID}_addUser" href="#addUser">{t _add_new_user}</a>
    			<span class="progressIndicator" style="display: none;"></span>
    		</li>
    		<li>
    			<a id="userGroup_{$userGroupID}_addUserCancel" href="#cancelAddingUser" class="hidden">{t _cancel_adding_new_user} </a>
    		</li>
    	</ul>  
        
        <fieldset id="newUserForm_{$userGroupID}" style="display: none;" class="newUserForm">
            {include file="backend/user/info.tpl"}
        </fieldset>
        
        {literal}
        <script type="text/javascript">
            try
            {
                Element.observe($("{/literal}userGroup_{$userGroupID}_addUser{literal}"), 'click', function(e)
                {
                    Backend.User.Add.prototype.getInstance({/literal}{$userGroupID}{literal}).showAddForm({/literal}{$userGroupID}{literal}); 
                    Event.stop(e);
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

<fieldset class="container" style="vertical-align: middle;">
                
    <span style="float: left; text-align: right;" id="userMass_{$userGroupID}">

	    {form action="controller=backend.user action=processMass id=$userGroupID" handle=$massForm style="vertical-align: middle;" onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        {t _with_selected}:
        <select name="act" class="select" style="width: auto;">
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
    
    <span style="float: right; text-align: right; position: relative; padding-bottom: 10px;">
		<span id="userCount_{$userGroupID}">
			<span class="rangeCount">Listing users %from - %to of %count</span>
			<span class="notFound">No users found</span>
		</span>    
		<br />
		<div style="padding-top: 5px;">
			<a href="#" onclick="Element.show($('userColumnMenu_{$userGroupID}')); return false;" style="margin-top: 15px;">{t Columns}</a>
		</div>
		<div id="userColumnMenu_{$userGroupID}" style="left: -250px; position: absolute; z-index: 5; width: auto; display: none;">
  		  <form action="{link controller=backend.user action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.parentNode.parentNode.parentNode.parentNode.parentNode, document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="POST">
			
			<input type="hidden" name="group" value="{$userGroupID}" />
			
			<div style="background-color: white; border: 1px solid black; float: right; text-align: center; white-space: nowrap; width: 250px;">
				<div style="padding: 5px; position: static; width: 100%;">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" name="sm" value="{tn Change columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('userColumnMenu_{$userGroupID}')); return false;" href="#cancel">{t _cancel}</a>
				</div>
			    <div style="padding: 10px; background-color: white; max-height: 300px; overflow: auto; text-align: left;">
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

<div style="width: 100%; position: relative;">
	<div style="display: none;" class="activeGrid_loadIndicator" id="userLoadIndicator_{$userGroupID}">
		<div>
			{t Loading data...}<span class="progressIndicator"></span>
		</div>
	</div>
</div>

<div style="width: 100%;height: 100%;">
<table class="activeGrid userList" id="users_{$userGroupID}" style="height: 100%;">
	<thead>
		<tr class="headRow">
	
			<th class="cell_cb"><input type="checkbox" class="checkbox" /></th>
			{foreach from=$displayedColumns item=type key=column name="columns"}
				{if !$smarty.foreach.columns.first}
					<th class="first cellt_{$type} cell_{$column|replace:'.':'_'}">
						<span class="fieldName">{$column}</span>
						{if 'bool' == $type}
				    		<select style="width: auto;" id="filter_{$column}_{$userGroupID}">
								<option value="">{tn $column}</option>
								<option value="1">{tn _yes}</option>
								<option value="0">{tn _no}</option>
							</select>					
						{else}
						<input type="text" class="text {$type}" id="filter_{$column}_{$userGroupID}" value="{$availableColumns.$column.name|escape}" />
						{/if}
					</th>		
				{/if}
			{/foreach}
		</tr>
	</thead>	
	<tbody>
		{section name="createRows" start=0 loop=15}
			<tr class="{if $smarty.section.createRows.index is even}even{else}odd{/if}">
				<td class="cell_cb"></td>
			{foreach from=$displayedColumns key=column item=type name="columns"}
  			 	{if !$smarty.foreach.columns.first}
					<td class="cellt_{$type} cell_{$column|replace:'.':'_'}"></td>		
				{/if}
			{/foreach}	
			</tr>	
		{/section}
	</tbody>
</table>
</div>

</div>

{literal}
<script type="text/javascript">
    try
    {
        var grid = new ActiveGrid($('{/literal}users_{$userGroupID}{literal}'), '{/literal}{link controller=backend.user action=lists}{literal}', {/literal}{$totalCount}{literal}, $("{/literal}userLoadIndicator_{$userGroupID}{literal}"));
    
    	grid.setDataFormatter(Backend.UserGroup.GridFormatter);
    	
    	{/literal}{foreach from=$displayedColumns item=id key=column name="columns"}{literal}
    		{/literal}{if !$smarty.foreach.columns.first}{literal}
    		    new ActiveGridFilter($('{/literal}filter_{$column}_{$userGroupID}{literal}'), grid);
    		{/literal}{/if}{literal}
    	{/literal}{/foreach}{literal}
        
        var massHandler = new Backend.UserGroup.massActionHandler($('{/literal}userMass_{$userGroupID}{literal}'), grid);
        massHandler.deleteConfirmMessage = '{/literal}{t _delete_conf|addslashes}{literal}' ;
        
        usersActiveGrid[{/literal}{$userGroupID}{literal}] = grid;
    }
    catch(e)
    {
        console.info(e);
    }
</script>
{/literal}