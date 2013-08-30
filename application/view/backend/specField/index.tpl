{% if empty(controller) %}
	{assign var=controller value="backend.specField"}
{% endif %}


<script type="text/javascript">
//<[!CDATA[
	/**<b></b>
	 * Create spec field prototype. Some fields are always the same
	 * so we define them in
	 */
	Backend.SpecField.prototype.links = {};
	Backend.SpecField.prototype.links.create		  = '{link controller=$controller action=create}';
	Backend.SpecField.prototype.links.update		  = '{link controller=$controller action=update}';
	Backend.SpecField.prototype.links.deleteField	 = '{link controller=$controller action=delete}/';
	Backend.SpecField.prototype.links.editField	   = '{link controller=$controller action=item}/';
	Backend.SpecField.prototype.links.sortField	   = '{link controller=$controller action=sort}/';
	Backend.SpecField.prototype.links.mergeValues	 = '{link controller="`$controller`Value" action=mergeValues}/';
	Backend.SpecField.prototype.links.deleteValue	 = '{link controller="`$controller`Value" action=delete}/';
	Backend.SpecField.prototype.links.sortValues	  = '{link controller="`$controller`Value" action=sort}/';
	Backend.SpecField.prototype.links.sortGroups	  = '{link controller="`$controller`Group" action=sort}/';
	Backend.SpecField.prototype.links.getGroup		= '{link controller="`$controller`Group" action=item}/';
	Backend.SpecField.prototype.links.deleteGroup	 = '{link controller="`$controller`Group" action=delete}/';
	Backend.SpecField.prototype.links.createGroup	 = '{link controller="`$controller`Group" action=create}';
	Backend.SpecField.prototype.links.updateGroup	 = '{link controller="`$controller`Group" action=update}';

	Backend.SpecField.prototype.msg = {};
	Backend.SpecField.prototype.msg.removeGroupQuestion  = '{t _SpecFieldGroup_remove_question|addslashes}';
	Backend.SpecField.prototype.msg.removeFieldQuestion  = '{t _SpecField_remove_question|addslashes}';
	Backend.SpecField.prototype.msg.editActiveListItem   = '{t _activeList_edit|addslashes}',
	Backend.SpecField.prototype.msg.deleteActiveListItem = '{t _activeList_delete|addslashes}'
	Backend.SpecField.prototype.activeListMessages =
	{
		'_activeList_edit':	Backend.SpecField.prototype.msg.editActiveListItem,
		'_activeList_delete':  Backend.SpecField.prototype.msg.deleteActiveListItem
	}


	{foreach from=$configuration item="configItem" key="configKey"}
		{% if $configKey == 'types' %}
			Backend.SpecField.prototype.[[configKey]] = Backend.SpecField.prototype.createTypesOptions({json array=$configItem});
		{% else %}
			Backend.SpecField.prototype.[[configKey]] = {json array=$configItem};
		{% endif %}
	{/foreach}

// ]!]>
</script>


<fieldset class="container" {denied role="category.update"}style="display: none"{/denied}>
	<ul class="menu" id="specField_menu_[[categoryID]]">
		<li class="addSpecField"><a href="#new" id="specField_item_new_[[categoryID]]_show">{t _add_new_field}</a></li>
		<li class="done addSpecFieldCancel" style="display: none;"><a href="#new" id="specField_item_new_[[categoryID]]_cancel">{t _cancel_adding_new_field}</a></li>

		<li class="addSpecFieldGroup"><a href="#new" id="specField_group_new_[[categoryID]]_show">{t _add_new_group}</a></li>
		<li class="done addSpecFieldGroupCancel" style="display: none;"><a href="#new" id="specField_group_new_[[categoryID]]_cancel">{t _cancel_adding_new_group}</a></li>
	</ul>
</fieldset>

<div>
	<fieldset class="addForm" id="specField_item_new_[[categoryID]]_form" style="display: none;">
		<legend>{t _add_new_field|capitalize}</legend>

		<script type="text/javascript">
		   var newSpecFieldForm = new Backend.SpecField('{json array=$specFieldsList}');
		   newSpecFieldForm.addField(null, "new" + Backend.SpecField.prototype.incValueCounter(), true);
		   newSpecFieldForm.bindDefaultFields();
		</script>

	</fieldset>

	<fieldset class="addForm" id="specField_group_new_[[categoryID]]_form" class="specField_new_group" style="display: none;">
		<legend>{t _add_new_group|capitalize}</legend>
		<script type="text/javascript">
		   new Backend.SpecFieldGroup($('specField_group_new_[[categoryID]]_form'), {ldelim} Category: {ldelim} ID: '[[categoryID]]' {rdelim} {rdelim});
		</script>
	</fieldset>
</div>

{* No group *}
<ul id="specField_items_list_[[categoryID]]_" class="specFieldList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit activeList_accept_specFieldList">
{assign var="lastSpecFieldGroup" value="-1"}
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
	{% if $field.SpecFieldGroup.ID %}{break}{% endif %}

	{% if $field.ID %}
	<li id="specField_items_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]_[[field.ID]]">
		<span class="specField_title">[[field.name_lang]]</span>
	</li>
	{% endif %}
{/foreach}
</ul>

{* Grouped specification fields *}
{assign var="lastSpecFieldGroup" value="-1"}
<ul id="specField_groups_list_[[categoryID]]" class="specFieldListGroup {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit activeListGroup">
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
	{% if !$field.SpecFieldGroup.ID %}{continue}{% endif %}

	{% if $lastSpecFieldGroup != $field.SpecFieldGroup.ID  %}
		{% if $lastSpecFieldGroup > 0 %}</ul></li>{% endif %}
		<li id="specField_groups_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]">
			<span class="specField_group_title">[[field.SpecFieldGroup.name_lang]]</span>
			<ul id="specField_items_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]" class="specFieldList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit  activeList_accept_specFieldList">
	{% endif %}

	{% if $field.ID %} {* For empty groups *}
	<li id="specField_items_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]_[[field.ID]]">
		<span class="specField_title">[[field.name_lang]]</span>
	</li>
	{% endif %}

	{% set lastSpecFieldGroup = $field.SpecFieldGroup.ID %}
{/foreach}
</ul>


<script type="text/javascript">
	 var categoryID = '[[categoryID]]';

	 Event.observe($("specField_item_new_"+categoryID+"_show"), "click", function(e)
	 {ldelim}
		 e.preventDefault();
		 Backend.SpecField.prototype.createNewAction(categoryID)
	 {rdelim});

	 Event.observe($("specField_group_new_"+categoryID+"_show"), "click", function(e)
	 {ldelim}
		 e.preventDefault();
		 Backend.SpecFieldGroup.prototype.createNewAction(categoryID);
	 {rdelim});

	 var groupList = ActiveList.prototype.getInstance('specField_groups_list_'+categoryID, Backend.SpecFieldGroup.prototype.callbacks, Backend.SpecField.prototype.msg.activeListMessages);
	 ActiveList.prototype.getInstance('specField_items_list_'+categoryID+'_', Backend.SpecField.prototype.callbacks, Backend.SpecField.prototype.activeListMessages);

	 {assign var="lastSpecFieldGroup" value="-1"}
	 {foreach item="field" from=$specFieldsWithGroups}
		 {% if $field.SpecFieldGroup && $lastSpecFieldGroupID != $field.SpecFieldGroup.ID %}
			  ActiveList.prototype.getInstance('specField_items_list_'+categoryID+'_[[field.SpecFieldGroup.ID]]', Backend.SpecField.prototype.callbacks, Backend.SpecField.prototype.activeListMessages);
		 {% endif %}
		 {% set lastSpecFieldGroupID = $field.SpecFieldGroup.ID %}
	 {/foreach}

	 groupList.createSortable(true);
</script>


