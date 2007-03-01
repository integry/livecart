{literal}
<script type="text/javascript">
//<[!CDATA[
    /**<b></b>
     * Create spec field prototype. Some fields are always the same
     * so we define them in
     */
    Backend.SpecField.prototype.links = {};
    Backend.SpecField.prototype.links.deleteField     = {/literal}'{link controller=backend.specField action=delete}/'{literal};
    Backend.SpecField.prototype.links.editField       = {/literal}'{link controller=backend.specField action=item}/'{literal};
    Backend.SpecField.prototype.links.sortField       = {/literal}'{link controller=backend.specField action=sort}/'{literal};
    Backend.SpecField.prototype.links.deleteValue     = {/literal}'{link controller=backend.specFieldValue action=delete}/'{literal};
    Backend.SpecField.prototype.links.sortValues      = {/literal}'{link controller=backend.specFieldValue action=sort}/'{literal};
    Backend.SpecField.prototype.links.sortGroups      = {/literal}'{link controller=backend.specFieldGroup action=sort}/'{literal};
    Backend.SpecField.prototype.links.getGroup        = {/literal}'{link controller=backend.specFieldGroup action=item}/'{literal};
    Backend.SpecField.prototype.links.deleteGroup     = {/literal}'{link controller=backend.specFieldGroup action=delete}/'{literal};

    Backend.SpecField.prototype.msg = {};
    Backend.SpecField.prototype.msg.removeGroupQuestion  = {/literal}'{t _SpecFieldGroup_remove_question|addslashes}'{literal};
    Backend.SpecField.prototype.msg.removeFieldQuestion  = {/literal}'{t _SpecField_remove_question|addslashes}'{literal};
    Backend.SpecField.prototype.msg.editActiveListItem   = {/literal}'{t _activeList_edit|addslashes}'{literal},
    Backend.SpecField.prototype.msg.deleteActiveListItem = {/literal}'{t _activeList_delete|addslashes}'{literal}
    Backend.SpecField.prototype.activeListMessages = 
    { 
        '_activeList_edit':    Backend.SpecField.prototype.msg.editActiveListItem,
        '_activeList_delete':  Backend.SpecField.prototype.msg.deleteActiveListItem
    }
    
    {/literal}
    {foreach from=$configuration item="configItem" key="configKey"}
        {if $configKey == 'types'}
            Backend.SpecField.prototype.{$configKey} = Backend.SpecField.prototype.createTypesOptions({json array=$configItem});
        {else}
            Backend.SpecField.prototype.{$configKey} = {json array=$configItem};
        {/if}
    {/foreach}
    
// ]!]>
</script>


<fieldset class="container">
	<ul class="menu" id="specField_menu_{$categoryID}">
	    <li><a href="#new" id="specField_item_new_{$categoryID}_show">{t _add_new_field}</a></li>
	    <li><a href="#new" id="specField_item_new_{$categoryID}_cancel" class="hidden">{t _cancel_adding_new_field}</a></li>
	    <li><a href="#new" id="specField_group_new_{$categoryID}_show">{t _add_new_group}</a></li>
	    <li><a href="#new" id="specField_group_new_{$categoryID}_cancel" class="hidden">{t _cancel_adding_new_group}</a></li>
	</ul>
</fieldset>

<div>
    <div id="specField_item_new_{$categoryID}_form" style="display: none;">
        <script type="text/javascript">
        try
        {literal}{{/literal}
           var newSpecFieldForm = new Backend.SpecField('{json array=$specFieldsList}');
           newSpecFieldForm.addField(null, "new" + Backend.SpecField.prototype.countNewFilters, true);
           newSpecFieldForm.bindDefaultFields();
           Backend.SpecField.prototype.countNewFilters++;
        {literal}}{/literal}
        catch(e)
        {literal}{{/literal}
            console.info(e)
        {literal}}{/literal}
        </script>
    </div>
    
    <div id="specField_group_new_{$categoryID}_form" class="specField_new_group" style="display: none;">
        <script type="text/javascript">
           new Backend.SpecFieldGroup($('specField_group_new_{$categoryID}_form'), {ldelim} Category: {ldelim} ID: {$categoryID} {rdelim} {rdelim});
        </script>
    </div>
</div>

<br />


{* No group *}
<ul id="specField_items_list_{$categoryID}_" class="specFieldList activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_specFieldList">
{assign var="lastSpecFieldGroup" value="-1"}
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
    {if $field.SpecFieldGroup.ID}{php}break;{/php}{/if}
     
    {if $field.ID} 
    <li id="specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}_{$field.ID}">
    	<span class="specField_title">{$field.name_lang}</span>
    </li>
    {/if}
{/foreach}
</ul>

{* Grouped specification fields *}
{assign var="lastSpecFieldGroup" value="-1"}
<ul id="specField_groups_list_{$categoryID}" class="specFieldListGroup activeList_add_sort activeList_add_edit activeList_add_delete">
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
    {if !$field.SpecFieldGroup.ID}{php}continue;{/php}{/if}
    
    {if $lastSpecFieldGroup != $field.SpecFieldGroup.ID }
        {if $lastSpecFieldGroup > 0}</ul></li>{/if}
        <li id="specField_groups_list_{$categoryID}_{$field.SpecFieldGroup.ID}">
            <span class="specField_group_title">{$field.SpecFieldGroup.name_lang}</span>   	
            <ul id="specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}" class="specFieldList activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_specFieldList">
    {/if}

    {if $field.ID} {* For empty groups *}
    <li id="specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}_{$field.ID}">
    	<span class="specField_title">{$field.name_lang}</span>
    </li>
    {/if}

    {assign var="lastSpecFieldGroup" value=$field.SpecFieldGroup.ID}
{/foreach}
</ul>


<script type="text/javascript">
     var categoryID = {$categoryID};
     var groupList = ActiveList.prototype.getInstance('specField_groups_list_'+categoryID, Backend.SpecFieldGroup.prototype.callbacks, Backend.SpecField.prototype.msg.activeListMessages);  
     
     Event.observe($("specField_item_new_"+categoryID+"_show"), "click", function(e) 
     {ldelim}
         Event.stop(e);
         Backend.SpecField.prototype.createNewAction(categoryID) 
     {rdelim});
     
     Event.observe($("specField_group_new_"+categoryID+"_show"), "click", function(e) 
     {ldelim}
         Event.stop(e); 
         Backend.SpecFieldGroup.prototype.createNewAction(categoryID);
     {rdelim});
 
 
    {assign var="lastSpecFieldGroup" value="-1"}
    {foreach item="field" from=$specFieldsWithGroups}
        {if $lastSpecFieldGroup != $field.SpecFieldGroup.ID}
            {if !$smarty.foreach.specFieldForeach.first}
                 console.count("create list");
                 ActiveList.prototype.getInstance('specField_items_list_'+categoryID+'_{$field.SpecFieldGroup.ID}', Backend.SpecField.prototype.callbacks, Backend.SpecField.prototype.activeListMessages);
            {/if}
            
            {assign var="lastSpecFieldGroup" value=$field.SpecFieldGroup.ID}
        {/if}
    {/foreach}
     
     groupList.createSortable();
</script>


