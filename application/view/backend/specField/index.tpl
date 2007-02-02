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
    {literal}

    specFieldListCallbacks = {
        beforeEdit:     function(li)
        {
            Backend.SpecField.prototype.hideNewSpecFieldAction({/literal}{$categoryID}{literal});
            
            if(this.isContainerEmpty(li, 'edit'))
            {
                return Backend.SpecField.prototype.links.editField + this.getRecordId(li)
            }
            else
            {
                this.toggleContainer(li, 'edit');
            }
        },
        afterEdit:      function(li, response)
        {
            var specField = eval("(" + response + ")" );
            specField.rootId = li.id;
            new Backend.SpecField(specField, true);
            this.rebindIcons(li);
            this.createSortable();
            this.toggleContainer(li, 'edit');
        },
        beforeDelete:   function(li)
        {
            if(confirm('{/literal}{t _SpecField_remove_question|addslashes}{literal}'))
            {
                return Backend.SpecField.prototype.links.deleteField + this.getRecordId(li)
            }
        },
        afterDelete:    function(li, jsonResponse)
        {
            var response = eval("("+jsonResponse+")");

            if(response.status == 'success') 
            {
                this.remove(li);
                CategoryTabControl.prototype.resetTabItemsCount({/literal}{$categoryID}{literal});
            }
        },
        beforeSort:     function(li, order)
        {
            return Backend.SpecField.prototype.links.sortField + "?target=" + this.ul.id + "&" + order
        },
        afterSort:      function(li, response)
        {
        }
    };
    
    
    specFieldGroupCallbacks = {
        beforeEdit:     function(li)
        {
            var groupTitle = document.getElementsByClassName("specField_group_title", li)[0];
            var input = groupTitle.getElementsByTagName("input")[0];
            var title = groupTitle.getElementsByTagName("span")[0];
            if('inline' != input.style.display) 
            {
                input.style.display = 'inline';
                title.style.display = 'none';
            }
            else
            {
                input.style.display = 'none';
                title.style.display = 'inline';
            }
            
        },
        afterEdit:      function(li, response)
        {
            console.info("group saved");
        },
        beforeDelete:   function(li)
        {
            console.info("delete group");
        },
        afterDelete:    function(li, jsonResponse)
        {
            console.info("group deleted");
        },
        beforeSort:     function(li, order)
        {
            return Backend.SpecField.prototype.links.sortField + "?target=specField_items_list_{/literal}{$categoryID}{literal}&" + order
        },
        afterSort:      function(li, response)
        {
        }
    };
// ]!]>
</script>
{/literal}

<div>
    <a href="#new" id="specField_item_new_{$categoryID}_show">{t _add_new_field}</a>
    <div id="specField_item_new_{$categoryID}_form" style="display: none;">
        <script type="text/javascript">
           var newSpecFieldForm = new Backend.SpecField('{json array=$specFieldsList}');
           newSpecFieldForm.addField(null, "new" + Backend.SpecField.prototype.countNewFilters, true);
           newSpecFieldForm.bindDefaultFields();
           Backend.SpecField.prototype.countNewFilters++;
        </script>
    </div>
</div>

<br />


<ul id="specField_groups_list_{$categoryID}" class="specFieldListGroup activeList_add_sort activeList_add_edit">
    {foreach item="group" from=$specFieldsGroups}
    <li id="specField_groups_list_{$categoryID}_{$group.ID}">
        <span class="specField_group_title">
            <span>{$group.name[$defaultLangCode]}</span>
            <input value="{$group.name[$defaultLangCode]}" />
        </span>
        
        <ul id="specField_items_list_{$categoryID}_{$group.ID}" class="specFieldList activeList_accept_specFieldList activeList_add_sort activeList_add_edit activeList_add_delete">
        {foreach item="field" from=$group.specFields}
        	<li id="specField_items_list_{$categoryID}_{$group.ID}_{$field.ID}">
            	<span class="specField_title">{$field.name[$defaultLangCode]}</span>
        	</li>
        {/foreach}
        </ul>
    </li>
    {/foreach}
</ul>


<script type="text/javascript">
    {literal}
     Backend.SpecField.prototype.activeListMessages = 
     { 
         '_activeList_edit':    {/literal}'{t _activeList_edit|addslashes}'{literal},
         '_activeList_delete':  {/literal}'{t _activeList_delete|addslashes}'{literal}
     }
     {/literal}
     
     var groupList = ActiveList.prototype.getInstance('specField_groups_list_{$categoryID}', specFieldGroupCallbacks, Backend.SpecField.prototype.activeListMessages);
     Event.observe($("specField_item_new_{$categoryID}_show"), "click", function(e) {literal}{{/literal} Backend.SpecField.prototype.createNewAction(e, '{$categoryID}') {literal}}{/literal});
     
     {foreach item="group" from=$specFieldsGroups}
         ActiveList.prototype.getInstance('specField_items_list_{$categoryID}_{$group.ID}', specFieldListCallbacks, Backend.SpecField.prototype.activeListMessages);
     {/foreach}
     
     groupList.createSortable();
</script>


