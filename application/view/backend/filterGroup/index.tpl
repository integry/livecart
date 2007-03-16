{literal}
<script type="text/javascript">
//<[!CDATA[
    Backend.Filter.prototype.links = {};
    Backend.Filter.prototype.links.deleteGroup = {/literal}'{link controller=backend.filterGroup action=delete}/'{literal};
    Backend.Filter.prototype.links.editGroup = {/literal}'{link controller=backend.filterGroup action=item}/'{literal};
    Backend.Filter.prototype.links.sortGroup = {/literal}'{link controller=backend.filterGroup action=sort}/'{literal};
    Backend.Filter.prototype.links.deleteFilter = {/literal}'{link controller=backend.filter action=delete}/'{literal};
    Backend.Filter.prototype.links.sortFilter = {/literal}'{link controller=backend.filter action=sort}/'{literal};
    Backend.Filter.prototype.links.generateFilters = {/literal}'{link controller=backend.filter action=generate}/'{literal};

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

// ]!]>
</script>
{/literal}

{if $blankFilter.specFields|@count > 0}
    <div>
        <fieldset class="container">
			<ul class="menu">
				<li><a href="#new" id="filter_item_new_{$categoryID}_show">{t _add_new_filter}</a></li>
			</ul>
		</fieldset>

        <div id="filter_item_new_{$categoryID}_form" style="display: none;" class="filter_item_new">
            <script type="text/javascript">
               {literal}try{{/literal}
                    var newFilterForm = new Backend.Filter('{json array=$blankFilter}');
                    newFilterForm.addFilter(null, "new" + Backend.Filter.prototype.countNewFilters, true);
                    newFilterForm.bindDefaultFields();
                    Backend.Filter.prototype.countNewFilters++;
               {literal}} catch(e) { console.info(e) }{/literal}
            </script>
        </div>
    </div>
    
    
    <br />
    
    <ul id="filter_items_list_{$categoryID}" class="filterList activeList_add_sort activeList_add_edit activeList_add_delete">
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
             _activeList_edit:    {/literal}'{t _activeList_edit|addslashes}'{literal},
             _activeList_delete:  {/literal}'{t _activeList_delete|addslashes}'{literal}
         }
         
         Event.observe($("filter_item_new_{/literal}{$categoryID}{literal}_show"), "click", function(e) { Backend.Filter.prototype.createNewAction(e, '{/literal}{$categoryID}{literal}') });
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