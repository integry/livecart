{literal}
<script type="text/javascript">
//<[!CDATA[
    /**
     * Create spec field prototype. Some fields are always the same
     * so we define them in
     */
        
    Backend.SpecField.prototype.links = {};
    Backend.SpecField.prototype.links.deleteField = {/literal}'{link controller=backend.specField action=delete}/'{literal};
    Backend.SpecField.prototype.links.editField = {/literal}'{link controller=backend.specField action=item}/'{literal};
    Backend.SpecField.prototype.links.sortField = {/literal}'{link controller=backend.specField action=sort}/'{literal};
    Backend.SpecField.prototype.links.deleteValue = {/literal}'{link controller=backend.specField action=deleteValue}/'{literal};
    Backend.SpecField.prototype.links.sortValues = {/literal}'{link controller=backend.specField action=sortValues}/'{literal};
    Backend.SpecField.prototype.links.sortGroups = {/literal}'{link controller=backend.specField action=sortGroups}/'{literal};
    Backend.SpecField.prototype.links.getGroup = {/literal}'{link controller=backend.specField action=group}/'{literal};
    Backend.SpecField.prototype.links.deleteGroup = {/literal}'{link controller=backend.specField action=deleteGroup}/'{literal};


    Backend.SpecField.prototype.msg = {};
    Backend.SpecField.prototype.msg.translateTo = {/literal}'{link controller=backend.specField action=sortValues}'{literal};
    
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


<div>
    <a href="#new" id="specField_item_new_{$categoryID}_show">{t _add_new_field}</a>
    <a href="#new" id="specField_group_new_{$categoryID}_show">{t _add_new_group}</a>
    
    <div id="specField_item_new_{$categoryID}_form" style="display: none;">
        <script type="text/javascript">
           var newSpecFieldForm = new Backend.SpecField('{json array=$specFieldsList}');
           newSpecFieldForm.addField(null, "new" + Backend.SpecField.prototype.countNewFilters, true);
           newSpecFieldForm.bindDefaultFields();
           Backend.SpecField.prototype.countNewFilters++;
        </script>
    </div>
    
    <div id="specField_group_new_{$categoryID}_form" class="specField_new_group" style="display: none;">
        <script type="text/javascript">
           new Backend.SpecFieldGroup($('specField_group_new_{$categoryID}_form'), {literal}
               { 
                   Category:
                   {
                       ID: {/literal}{$categoryID}{literal}
                   }
               }{/literal});
        </script>
    </div>
</div>

<br />



{* No group *}
<span class="specField_group_title">{t _not_in_group}</span>
<ul id="specField_items_list_{$categoryID}_" class="specFieldList activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_specFieldList">
{assign var="lastSpecFieldGroup" value="-1"}
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
    {if $field.SpecFieldGroup.ID}{php}break;{/php}{/if}
     
    {if $field.ID} 
    <li id="specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}_{$field.ID}">
    	<span class="specField_title">{$field.name[$defaultLangCode]}</span>
    </li>
    {/if}
{/foreach}
</ul>
<hr />

{* Grouped specification fields *}
{assign var="lastSpecFieldGroup" value="-1"}
<ul id="specField_groups_list_{$categoryID}" class="specFieldListGroup activeList_add_sort activeList_add_edit activeList_add_delete">
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
    {if !$field.SpecFieldGroup.ID}{php}continue;{/php}{/if}
    
    {if $lastSpecFieldGroup != $field.SpecFieldGroup.ID }
        {if $lastSpecFieldGroup > 0}</ul></li>{/if}
        <li id="specField_groups_list_{$categoryID}_{$field.SpecFieldGroup.ID}">
            <span class="specField_group_title">{$field.SpecFieldGroup.name[$defaultLangCode]}</span>   	
            <ul id="specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}" class="specFieldList activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_specFieldList">
    {/if}


    {if $field.ID} {* For empty groups *}
    <li id="specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}_{$field.ID}">
    	<span class="specField_title">{$field.name[$defaultLangCode]}</span>
    </li>
    {/if}

    {assign var="lastSpecFieldGroup" value=$field.SpecFieldGroup.ID}
{/foreach}
</ul>


<script type="text/javascript">
{literal}
    var specFieldListCallbacks = {
        beforeEdit:     function(li) {
            Backend.SpecField.prototype.hideNewSpecFieldAction({/literal}{$categoryID}{literal});
            
            if(this.isContainerEmpty(li, 'edit')) return Backend.SpecField.prototype.links.editField + this.getRecordId(li)
            else this.toggleContainer(li, 'edit');
        },
        afterEdit:      function(li, response) {
            var specField = eval("(" + response + ")" );
            specField.rootId = li.id;
            new Backend.SpecField(specField, true);
            this.rebindIcons(li);
            this.createSortable();
            this.toggleContainer(li, 'edit');
        },
        beforeDelete:   function(li) {
            if(confirm('{/literal}{t _SpecField_remove_question|addslashes}{literal}'))
            return Backend.SpecField.prototype.links.deleteField + this.getRecordId(li)
        },
        afterDelete:    function(li, jsonResponse)
        {
            var response = eval("("+jsonResponse+")");
            if(response.status == 'success') {
                this.remove(li);
                CategoryTabControl.prototype.resetTabItemsCount({/literal}{$categoryID}{literal});
            }
        },
        beforeSort:     function(li, order) {
            return Backend.SpecField.prototype.links.sortField + "?target=" + this.ul.id + "&" + order
        },
        afterSort:     function(li, order) {    }
    };
    
    /**
     * Group
     */
    var specFieldGroupCallbacks = {
        beforeEdit:     function(li) 
        {
            try
            {
                if(!Backend.SpecFieldGroup.prototype.isGroupTranslated(li))
                {
                    return Backend.SpecField.prototype.links.getGroup + this.getRecordId(li);
                }
                else
                {
                    console.info('ddd', document.getElementsByClassName('specField_group_form', li)[0].style.display);
                    if('block' != document.getElementsByClassName('specField_group_form', li)[0].style.display)
                    {
                         Backend.SpecFieldGroup.prototype.displayGroupTranslations(li);
                    }
                    else
                    {
                         Backend.SpecFieldGroup.prototype.hideGroupTranslations(li);
                    }   
                }
            } 
            catch(e) 
            {  
                console.info(e) 
            }
        },
        afterEdit:      function(li, response) { 
            try
            {
                console.info('adasd');
                console.info(li);
                new Backend.SpecFieldGroup(li, eval("(" + response + ")"));
                Backend.SpecFieldGroup.prototype.displayGroupTranslations(li);  
            } 
            catch(e) 
            {  
                console.info(e) 
            }
        },
        beforeDelete:   function(li) {
            if(confirm('{/literal}{t _SpecFieldGroup_remove_question|addslashes}{literal}'))
            return Backend.SpecField.prototype.links.deleteGroup + this.getRecordId(li)
        },
        afterDelete:    function(li, jsonResponse)
        {
            var response = eval("("+jsonResponse+")");
            if(response.status == 'success') {
                this.remove(li);
                CategoryTabControl.prototype.resetTabItemsCount({/literal}{$categoryID}{literal});
            }
        },
        beforeSort:     function(li, order) {
            return Backend.SpecField.prototype.links.sortGroups + "?target=" + this.ul.id + "&" + order
        },
        afterSort:     function(li, order) { }
    };


     Backend.SpecField.prototype.activeListMessages = 
     { 
         '_activeList_edit':    {/literal}'{t _activeList_edit|addslashes}'{literal},
         '_activeList_delete':  {/literal}'{t _activeList_delete|addslashes}'{literal}
     }
     {/literal}
     
     var groupList = ActiveList.prototype.getInstance('specField_groups_list_{$categoryID}', specFieldGroupCallbacks, Backend.SpecField.prototype.activeListMessages);
     Event.observe($("specField_item_new_{$categoryID}_show"), "click", function(e) {literal}{{/literal} Backend.SpecField.prototype.createNewAction(e, '{$categoryID}') {literal}}{/literal});
     Event.observe($("specField_group_new_{$categoryID}_show"), "click", function(e) {literal}{{/literal} Event.stop(e); Backend.SpecFieldGroup.prototype.createNewAction('{$categoryID}') {literal}}{/literal});

 
{assign var="lastSpecFieldGroup" value="-1"}
{foreach item="field" from=$specFieldsWithGroups}
    {if $lastSpecFieldGroup != $field.SpecFieldGroup.ID}
        {if !$smarty.foreach.specFieldForeach.first}
             ActiveList.prototype.getInstance('specField_items_list_{$categoryID}_{$field.SpecFieldGroup.ID}', specFieldListCallbacks, Backend.SpecField.prototype.activeListMessages);
        {/if}
        
        {assign var="lastSpecFieldGroup" value=$field.SpecFieldGroup.ID}
    {/if}
{/foreach}
     
     groupList.createSortable();
</script>


