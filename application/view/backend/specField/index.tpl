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
            new Backend.SpecField(response);

            this.rebindIcons(li);
            this.createSortable();

            this.toggleContainer(li, 'edit');
        },
        beforeDelete:   function(li)
        {
            if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?'))
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
            }
        },


        beforeSort:     function(li, order)
        {
            return Backend.SpecField.prototype.links.sortField + '?' + order
        },
        afterSort:      function(li, response)
        {
//            alert( 'Record #' + this.getRecordId(li, 'edit') + ' changed position');
        }
    };
// ]!]>
</script>
{/literal}

<div>
    <a href="#new" id="specField_item_new_{$categoryID}_show">{t _add_new_field}</a>
    <div id="specField_item_new_{$categoryID}_form" style="display: none;">
        <script type="text/javascript">
        new Backend.SpecField('{json array=$specFieldsList}');
        </script>
    </div>
</div>

<br />

<ul id="specField_items_list_{$categoryID}" class="specFieldList  activeList_add_sort activeList_add_edit activeList_add_delete">
{foreach item="field" from=$specFields}
	<li id="specField_items_list_{$categoryID}_{$field.ID}">
    	<span class="specField_title">{$field.name[$defaultLangCode]}</span>
	</li>
{/foreach}

</ul>


{literal}
<script type="text/javascript">
     $("specField_item_new_{/literal}{$categoryID}{literal}_show").onclick = function(e) { Backend.SpecField.prototype.createNewAction(e, '{/literal}{$categoryID}{literal}') }
     window.activeSpecFieldsList = new ActiveList('specField_items_list_{/literal}{$categoryID}{literal}', specFieldListCallbacks);
</script>
{/literal}