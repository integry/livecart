{literal}
<script type="text/javascript">
//<[!CDATA[
    /**
     * Create spec field prototype. Some fields are always the same
     * so we define them in
     */
    Backend.Filter.prototype.links = {};
    Backend.Filter.prototype.links.deleteGroup = {/literal}'{link controller=backend.filter action=delete}/'{literal};
    Backend.Filter.prototype.links.editGroup = {/literal}'{link controller=backend.filter action=item}/'{literal};
    Backend.Filter.prototype.links.sortGroup = {/literal}'{link controller=backend.filter action=sort}/'{literal};
    Backend.Filter.prototype.links.deleteFilter = {/literal}'{link controller=backend.filter action=deleteFilter}/'{literal};
    Backend.Filter.prototype.links.sortFilter = {/literal}'{link controller=backend.filter action=sortFilter}/'{literal};
    Backend.Filter.prototype.links.generateFilters = {/literal}'{link controller=backend.filter action=generateFilters}/'{literal};

    Backend.Filter.prototype.msg = {};
    Backend.Filter.prototype.msg.translateTo = {/literal}'{t _translate_to}'{literal};
    
    {/literal}
    {foreach from=$configuration item="configItem" key="configKey"}
        {if $configKey == 'types'}
            Backend.Filter.prototype.{$configKey} = Backend.Filter.prototype.createTypesOptions({json array=$configItem});
        {else}
            Backend.Filter.prototype.{$configKey} = {json array=$configItem};
        {/if}
    {/foreach}
    {literal}


    filterListCallbacks = {
        beforeEdit:     function(li)
        {
            Backend.Filter.prototype.hideNewFilterAction({/literal}{$categoryID}{literal});
            
            if(this.isContainerEmpty(li, 'edit')) return Backend.Filter.prototype.links.editGroup + this.getRecordId(li)
            else this.toggleContainer(li, 'edit');
        },


        afterEdit:      function(li, response)
        {
            new Backend.Filter(response);

            this.rebindIcons(li);
            this.createSortable();

            this.toggleContainer(li, 'edit');
        },

        beforeDelete:   function(li)
        {
            if(confirm('Are you sure you wish to remove record #' + this.getRecordId(li) + '?'))  return Backend.Filter.prototype.links.deleteGroup + this.getRecordId(li)
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
            return Backend.Filter.prototype.links.sortGroup + '?target=' + "filter_items_list_{/literal}{$categoryID}{literal}&" + order
        },


        afterSort:      function(li, response) { }
    };
// ]!]>
</script>
{/literal}

{if $blankFilter.specFields|@count > 0}
    <div>
        <a href="#new" id="filter_item_new_{$categoryID}_show">{t _add_new_filter}</a>
        <div id="filter_item_new_{$categoryID}_form" style="display: none;">
            <script type="text/javascript"> new Backend.Filter('{json array=$blankFilter}');</script>
        </div>
    </div>
    
    
    <br />
    
    <ul id="filter_items_list_{$categoryID}" class="filterList activeList_add_sort activeList_add_edit activeList_add_delete">
    {foreach item="filter" from=$filters}
    	<li id="filter_items_list_{$categoryID}_{$filter.ID}">
        	<span class="filter_title">{$filter.name[$defaultLangCode]}</span>
    	</li>
    {/foreach}
    </ul>
    
    {literal}
    <script type="text/javascript">
        Backend.Filter.prototype.activeListMessages = 
        { 
            _activeList_edit:    {/literal}'{t _activeList_edit|addslashes}'{literal},
            _activeList_delete:  {/literal}'{t _activeList_delete|addslashes}'{literal}
        }
         $("filter_item_new_{/literal}{$categoryID}{literal}_show").onclick = function(e) { Backend.Filter.prototype.createNewAction(e, '{/literal}{$categoryID}{literal}') }
         window.activeFiltersList[{/literal}{$categoryID}{literal}] = new ActiveList('filter_items_list_{/literal}{$categoryID}{literal}', filterListCallbacks, Backend.Filter.prototype.activeListMessages);
    </script>
    {/literal}
{else}
    <div class="errorMessage">
        <div>
        <h3>{t _category_has_no_attr}</h3>
        <p>{t _add_attr_to_create_filters}</p>
        </div>
    </div>
{/if}