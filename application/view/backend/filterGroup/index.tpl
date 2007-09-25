{literal}
<script type="text/javascript">
//<[!CDATA[
    Backend.Filter.prototype.links = {};
    Backend.Filter.prototype.links.deleteGroup = '{/literal}{link controller=backend.filterGroup action=delete}/{literal}';
    Backend.Filter.prototype.links.editGroup = '{/literal}{link controller=backend.filterGroup action=item}/{literal}';
    Backend.Filter.prototype.links.sortGroup = '{/literal}{link controller=backend.filterGroup action=sort}/{literal}';
    Backend.Filter.prototype.links.updateGroup = '{/literal}{link controller=backend.filterGroup action=update}/{literal}';
    Backend.Filter.prototype.links.createGroup = '{/literal}{link controller=backend.filterGroup action=create}/{literal}';
    Backend.Filter.prototype.links.deleteFilter = '{/literal}{link controller=backend.filter action=delete}/{literal}';
    Backend.Filter.prototype.links.sortFilter = '{/literal}{link controller=backend.filter action=sort}/{literal}';
    Backend.Filter.prototype.links.generateFilters = '{/literal}{link controller=backend.filter action=generate}/{literal}';

    Backend.Filter.prototype.msg = {};
    Backend.Filter.prototype.msg.translateTo = '{/literal}{t _translate_to}{literal}';
    Backend.Filter.prototype.msg.removeQuestion = '{/literal}{t _FilterGroup_remove_question|addslashes}{literal}';
    {/literal}
    {foreach from=$configuration item="configItem" key="configKey"}
        {if $configKey == 'types'}
            Backend.Filter.prototype.{$configKey} = Backend.Filter.prototype.createTypesOptions({json array=$configItem});
        {else}
            Backend.Filter.prototype.{$configKey} = {json array=$configItem};
        {/if}
    {/foreach}
    {literal}

// ]!]>
</script>
{/literal}

{if $blankFilter.specFields|@count > 0}
    <div>
        <fieldset class="container" {denied role="category.update"}style="display: none"{/denied}>
			<ul class="menu" id="filter_new_{$categoryID}_menu">
				<li class="filter_add"><a href="#new" id="filter_item_new_{$categoryID}_show">{t _add_new_filter}</a></li>
				<li class="filter_addCancel" style="display: none"><a href="#new" id="filter_item_new_{$categoryID}_cancel">{t _cancel_adding_new_filter}</a></li>
			</ul>
		</fieldset>

        <fieldset class="addForm" id="filter_item_new_{$categoryID}_form" style="display: none;" class="filter_item_new">
            <legend>{t _add_new_filter}</legend>
			<script type="text/javascript">
                var newFilterForm = new Backend.Filter({json array=$blankFilter}, true);
                newFilterForm.addFilter(null, "new" + Backend.Filter.prototype.countNewFilters, true);
                newFilterForm.bindDefaultFields();
                Backend.Filter.prototype.countNewFilters++;
            </script>
        </fieldset>
    </div>
        
    <ul id="filter_items_list_{$categoryID}" class="filterList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit ">
    {foreach item="filter" from=$filters}
    	<li id="filter_items_list_{$categoryID}_{$filter.ID}" {if $filter.filtersCount == 0}class="filtergroup_has_no_filters"{/if}>
        	<span class="filter_title">{$filter.name_lang}</span> <span class="filter_count">({$filter.filtersCount})</span>
    	</li>
    {/foreach}
    </ul>
    
    {literal}
    <script type="text/javascript">
         Backend.Filter.prototype.activeListMessages = 
         { 
             _activeList_edit:    '{/literal}{t _activeList_edit|addslashes}{literal}',
             _activeList_delete:  '{/literal}{t _activeList_delete|addslashes}{literal}'
         }
         
         Event.observe($("filter_item_new_{/literal}{$categoryID}{literal}_show"), "click", function(e) 
         { 
             Event.stop(e);
             Backend.Filter.prototype.createNewAction('{/literal}{$categoryID}{literal}') 
         });
         ActiveList.prototype.getInstance('filter_items_list_{/literal}{$categoryID}{literal}', Backend.Filter.prototype.activeListCallbacks, Backend.Filter.prototype.activeListMessages);
    </script>
    {/literal}
{else}
    <div class="noRecords">
        <div>
        {t _category_has_no_attr}
        </div>
    </div>
{/if}