
<script type="text/javascript">
//<[!CDATA[
	Backend.Filter.prototype.links = {};
	Backend.Filter.prototype.links.deleteGroup = '{link controller="backend.filterGroup" action=delete}/';
	Backend.Filter.prototype.links.editGroup = '{link controller="backend.filterGroup" action=item}/';
	Backend.Filter.prototype.links.sortGroup = '{link controller="backend.filterGroup" action=sort}/';
	Backend.Filter.prototype.links.updateGroup = '{link controller="backend.filterGroup" action=update}/';
	Backend.Filter.prototype.links.createGroup = '{link controller="backend.filterGroup" action=create}/';
	Backend.Filter.prototype.links.deleteFilter = '{link controller="backend.filter" action=delete}/';
	Backend.Filter.prototype.links.sortFilter = '{link controller="backend.filter" action=sort}/';
	Backend.Filter.prototype.links.generateFilters = '{link controller="backend.filter" action=generate}/';

	Backend.Filter.prototype.msg = {};
	Backend.Filter.prototype.msg.translateTo = '{t _translate_to}';
	Backend.Filter.prototype.msg.removeQuestion = '{t _FilterGroup_remove_question|addslashes}';

	{foreach from=$configuration item="configItem" key="configKey"}
		{% if $configKey == 'types' %}
			Backend.Filter.prototype.[[configKey]] = Backend.Filter.prototype.createTypesOptions({json array=$configItem});
		{% else %}
			Backend.Filter.prototype.[[configKey]] = {json array=$configItem};
		{% endif %}
	{/foreach}

// ]!]>
</script>


{% if $blankFilter.specFields|@count > 0 %}
	<div>
		<fieldset class="container" {denied role="category.update"}style="display: none"{/denied}>
			<ul class="menu" id="filter_new_[[categoryID]]_menu">
				<li class="filter_add"><a href="#new" id="filter_item_new_[[categoryID]]_show">{t _add_new_filter}</a></li>
				<li class="filter_addCancel done" style="display: none"><a href="#new" id="filter_item_new_[[categoryID]]_cancel">{t _cancel_adding_new_filter}</a></li>
			</ul>
		</fieldset>

		<fieldset class="addForm" id="filter_item_new_[[categoryID]]_form" style="display: none;" class="filter_item_new">
			<legend>{t _add_new_filter|capitalize}</legend>
			<script type="text/javascript">
				var newFilterForm = new Backend.Filter({json array=$blankFilter}, true);
				newFilterForm.addFilter(null, "new" + Backend.Filter.prototype.countNewFilters, true);
				newFilterForm.bindDefaultFields();
				Backend.Filter.prototype.countNewFilters++;
			</script>
		</fieldset>
	</div>

	<ul id="filter_items_list_[[categoryID]]" class="filterList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit ">
	{foreach item="filter" from=$filters}
		<li id="filter_items_list_[[categoryID]]_[[filter.ID]]" {% if $filter.filtersCount == 0 %}class="filtergroup_has_no_filters"{% endif %}>
			<span class="filter_title">[[filter.name_lang]]</span> <span class="filter_count">([[filter.filtersCount]])</span>
		</li>
	{/foreach}
	</ul>


	<script type="text/javascript">
		 Backend.Filter.prototype.activeListMessages =
		 {
			 _activeList_edit:	'{t _activeList_edit|addslashes}',
			 _activeList_delete:  '{t _activeList_delete|addslashes}'
		 }

		 Event.observe($("filter_item_new_[[categoryID]]_show"), "click", function(e)
		 {
			 e.preventDefault();
			 Backend.Filter.prototype.createNewAction('[[categoryID]]')
		 });
		 ActiveList.prototype.getInstance('filter_items_list_[[categoryID]]', Backend.Filter.prototype.activeListCallbacks, Backend.Filter.prototype.activeListMessages);
	</script>

{% else %}
	<div class="noRecords">
		<div>
		{t _category_has_no_attr}
		</div>
	</div>
{% endif %}