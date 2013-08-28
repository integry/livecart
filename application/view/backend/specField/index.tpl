{if !$controller}
	{assign var=controller value="backend.specField"}
{/if}

{literal}
<script type="text/javascript">
//<[!CDATA[
	/**<b></b>
	 * Create spec field prototype. Some fields are always the same
	 * so we define them in
	 */
	Backend.SpecField.prototype.links = {};
	Backend.SpecField.prototype.links.create		  = {/literal}'{link controller=$controller action=create}'{literal};
	Backend.SpecField.prototype.links.update		  = {/literal}'{link controller=$controller action=update}'{literal};
	Backend.SpecField.prototype.links.deleteField	 = {/literal}'{link controller=$controller action=delete}/'{literal};
	Backend.SpecField.prototype.links.editField	   = {/literal}'{link controller=$controller action=item}/'{literal};
	Backend.SpecField.prototype.links.sortField	   = {/literal}'{link controller=$controller action=sort}/'{literal};
	Backend.SpecField.prototype.links.mergeValues	 = {/literal}'{link controller="`$controller`Value" action=mergeValues}/'{literal};
	Backend.SpecField.prototype.links.deleteValue	 = {/literal}'{link controller="`$controller`Value" action=delete}/'{literal};
	Backend.SpecField.prototype.links.sortValues	  = {/literal}'{link controller="`$controller`Value" action=sort}/'{literal};
	Backend.SpecField.prototype.links.sortGroups	  = {/literal}'{link controller="`$controller`Group" action=sort}/'{literal};
	Backend.SpecField.prototype.links.getGroup		= {/literal}'{link controller="`$controller`Group" action=item}/'{literal};
	Backend.SpecField.prototype.links.deleteGroup	 = {/literal}'{link controller="`$controller`Group" action=delete}/'{literal};
	Backend.SpecField.prototype.links.createGroup	 = {/literal}'{link controller="`$controller`Group" action=create}'{literal};
	Backend.SpecField.prototype.links.updateGroup	 = {/literal}'{link controller="`$controller`Group" action=update}'{literal};

	Backend.SpecField.prototype.msg = {};
	Backend.SpecField.prototype.msg.removeGroupQuestion  = {/literal}'{t _SpecFieldGroup_remove_question|addslashes}'{literal};
	Backend.SpecField.prototype.msg.removeFieldQuestion  = {/literal}'{t _SpecField_remove_question|addslashes}'{literal};
	Backend.SpecField.prototype.msg.editActiveListItem   = {/literal}'{t _activeList_edit|addslashes}'{literal},
	Backend.SpecField.prototype.msg.deleteActiveListItem = {/literal}'{t _activeList_delete|addslashes}'{literal}
	Backend.SpecField.prototype.activeListMessages =
	{
		'_activeList_edit':	Backend.SpecField.prototype.msg.editActiveListItem,
		'_activeList_delete':  Backend.SpecField.prototype.msg.deleteActiveListItem
	}

	{/literal}
	{foreach from=$configuration item="configItem" key="configKey"}
		{if $configKey == 'types'}
			Backend.SpecField.prototype.[[configKey]] = Backend.SpecField.prototype.createTypesOptions({json array=$configItem});
		{else}
			Backend.SpecField.prototype.[[configKey]] = {json array=$configItem};
		{/if}
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
		{literal}
		<script type="text/javascript">
		   var newSpecFieldForm = new Backend.SpecField('{/literal}{json array=$specFieldsList}{literal}');
		   newSpecFieldForm.addField(null, "new" + Backend.SpecField.prototype.incValueCounter(), true);
		   newSpecFieldForm.bindDefaultFields();
		</script>
		{/literal}
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
	{if $field.SpecFieldGroup.ID}{break}{/if}

	{if $field.ID}
	<li id="specField_items_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]_[[field.ID]]">
		<span class="specField_title">[[field.name_lang]]</span>
	</li>
	{/if}
{/foreach}
</ul>

{* Grouped specification fields *}
{assign var="lastSpecFieldGroup" value="-1"}
<ul id="specField_groups_list_[[categoryID]]" class="specFieldListGroup {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit activeListGroup">
{foreach name="specFieldForeach" item="field" from=$specFieldsWithGroups}
	{if !$field.SpecFieldGroup.ID}{continue}{/if}

	{if $lastSpecFieldGroup != $field.SpecFieldGroup.ID }
		{if $lastSpecFieldGroup > 0}</ul></li>{/if}
		<li id="specField_groups_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]">
			<span class="specField_group_title">[[field.SpecFieldGroup.name_lang]]</span>
			<ul id="specField_items_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]" class="specFieldList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit  activeList_accept_specFieldList">
	{/if}

	{if $field.ID} {* For empty groups *}
	<li id="specField_items_list_[[categoryID]]_[[field.SpecFieldGroup.ID]]_[[field.ID]]">
		<span class="specField_title">[[field.name_lang]]</span>
	</li>
	{/if}

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
		 {if $field.SpecFieldGroup && $lastSpecFieldGroupID != $field.SpecFieldGroup.ID}
			  ActiveList.prototype.getInstance('specField_items_list_'+categoryID+'_[[field.SpecFieldGroup.ID]]', Backend.SpecField.prototype.callbacks, Backend.SpecField.prototype.activeListMessages);
		 {/if}
		 {% set lastSpecFieldGroupID = $field.SpecFieldGroup.ID %}
	 {/foreach}

	 groupList.createSortable(true);
</script>


