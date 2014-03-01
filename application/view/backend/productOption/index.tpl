
<script type="text/javascript">
//<[!CDATA[
	/**<b></b>
	 * Create spec field prototype. Some fields are always the same
	 * so we define them in
	 */
	Backend.ProductOption.prototype.links = {};
	Backend.ProductOption.prototype.links.create		  = '[[ url("backend.productOption/create") ]]';
	Backend.ProductOption.prototype.links.update		  = '[[ url("backend.productOption/update") ]]';
	Backend.ProductOption.prototype.links.deleteField	 = '[[ url("backend.productOption/delete") ]]/';
	Backend.ProductOption.prototype.links.editField	   = '[[ url("backend.productOption/item") ]]/';
	Backend.ProductOption.prototype.links.sortField	   = '[[ url("backend.productOption/sort") ]]/';
	Backend.ProductOption.prototype.links.deleteValue	 = '[[ url("backend.productOption/deleteChoice") ]]/';
	Backend.ProductOption.prototype.links.sortValues	  = '[[ url("backend.productOption/sortChoice") ]]/';

	Backend.ProductOption.prototype.msg = {};
	Backend.ProductOption.prototype.msg.removeFieldQuestion  = '[[ addslashes({t _ProductOption_remove_question}) ]]';
	Backend.ProductOption.prototype.msg.editActiveListItem   = '[[ addslashes({t _activeList_edit}) ]]',
	Backend.ProductOption.prototype.msg.deleteActiveListItem = '[[ addslashes({t _activeList_delete}) ]]'
	Backend.ProductOption.prototype.activeListMessages =
	{
		'_activeList_edit':	Backend.ProductOption.prototype.msg.editActiveListItem,
		'_activeList_delete':  Backend.ProductOption.prototype.msg.deleteActiveListItem
	}


	{foreach from=$configuration item="configItem" key="configKey"}
		{% if $configKey == 'types' %}
			Backend.ProductOption.prototype.[[configKey]] = Backend.ProductOption.prototype.createTypesOptions({json array=$configItem});
		{% else %}
			Backend.ProductOption.prototype.[[configKey]] = {json array=$configItem};
		{% endif %}
	{/foreach}

// ]!]>
</script>


<fieldset class="container">
	<ul class="menu" id="productOption_menu_[[parentID]]">
		<li class="addProductOption"><a href="#new" id="productOption_item_new_[[parentID]]_show">{t _add_new_field}</a></li>
		<li class="done addProductOptionCancel" style="display: none;"><a href="#new" id="productOption_item_new_[[parentID]]_cancel">{t _cancel_adding_new_field}</a></li>
	</ul>
</fieldset>

<div>
	<fieldset class="addForm" id="productOption_item_new_[[parentID]]_form" style="display: none;">
		<legend>[[ capitalize({t _add_new_field}) ]]</legend>

		<script type="text/javascript">
		   var newProductOptionForm = new Backend.ProductOption('{json array=$productOptionsList}');
		   newProductOptionForm.addField(null, "new" + Backend.ProductOption.prototype.countNewFilters, true);
		   newProductOptionForm.bindDefaultFields();
		   Backend.ProductOption.prototype.countNewFilters++;
		</script>

	</fieldset>
</div>

{* No group *}
<ul id="productOption_items_list_[[parentID]]_" class="productOptionList activeList_add_sort activeList_add_delete activeList_add_edit activeList_accept_productOptionList">
{assign var="lastProductOptionGroup" value="-1"}
{foreach name="productOptionForeach" item="field" from=$options}
	{% if $field.ID %}
		<li id="productOption_items_list_[[parentID]]_[[field.ProductOptionGroup.ID]]_[[field.ID]]">
			<span class="productOption_title">[[field.name()]]</span>
			<span class="productOption_choiceCount"></span>
		</li>
	{% endif %}
{/foreach}
</ul>

<script type="text/javascript">
	 var parentID = '[[parentID]]';

	 Event.observe($("productOption_item_new_"+parentID+"_show"), "click", function(e)
	 {ldelim}
		 e.preventDefault();
		 Backend.ProductOption.prototype.createNewAction(parentID)
	 {rdelim});

	 ActiveList.prototype.getInstance('productOption_items_list_'+parentID+'_', Backend.ProductOption.prototype.callbacks, Backend.ProductOption.prototype.activeListMessages);

</script>