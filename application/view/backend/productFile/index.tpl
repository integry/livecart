<div id="productFileMsg_[[productID]]" style="display: none;"></div>

{* upper menu *}
<fieldset class="container" {denied role="product.update"}style="dispaly: none"{/denied}>
	<ul class="menu" id="productFile_menu_[[productID]]">
		<li class="productFile_add"><a href="#new_file" id="productFile_new_[[productID]]_show">{t _add_new_file}</a></li>
		<li class="productFile_addCancel done" style="display: none"><a href="#cancel_group" id="productFile_new_[[productID]]_cancel">{t _cancel_adding_new_file}</a></li>
		<li class="productFileGroup_add"><a href="#new_group" id="productFileGroup_new_[[productID]]_show">{t _add_new_group}</a></li>
		<li class="productFileGroup_addCancel done" style="display: none"><a href="#cancel_group" id="productFileGroup_new_[[productID]]_cancel">{t _cancel_adding_file_group}</a></li>
	</ul>
</fieldset>

{* new form *}
<div id="productFileGroup_new_[[productID]]_form"></div>
<div class="addForm" id="productFile_new_[[productID]]_form"></div>

{* Files with no group *}
<ul id="productFile_list_[[productID]]_" class="productFile_list {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit activeList_accept_productFile_list">
{foreach item="productFile" from=$productFilesWithGroups}
	{% if $productFile.ProductFileGroup.ID %}{break}{% endif %}
	{% if $productFile.ID %}
		<li id="productFile_list_[[productID]]_[[productFile.ProductFileGroup.ID]]_[[productFile.ID]]">
			<span class="productFile_item_title">[[productFile.title()]]</span>
		</li>
	{% endif %}
{/foreach}
</ul>

{* Files in groups *}
<ul id="productFileGroup_list_[[productID]]" class="activeListGroup {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit productFileGroup_list">
{foreach item="productFile" from=$productFilesWithGroups}
	{% if !$productFile.ProductFileGroup.ID %}{continue}{% endif %}

	{% if $lastProductFileGroup != $productFile.ProductFileGroup.ID  %}
		{% if $lastProductFileGroup > 0 %}</ul></li>{% endif %}
		<li id="productFileGroup_list_[[productID]]_[[productFile.ProductFileGroup.ID]]" class="productFileGroup_item">
			<span class="productFileGroup_title">[[productFile.ProductFileGroup.name()]]</span>
			<div id="activeList_editContainer_productFileGroup_list_[[productID]]_[[productFile.ProductFileGroup.ID]]" class="activeList_editContainer activeList_container" style="display: none">
				[[ partial("backend/productFileGroup/form.tpl") ]]
			</div>
			<ul id="productFile_list_[[productID]]_[[productFile.ProductFileGroup.ID]]" class="productFile_list {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit activeList_accept_productFile_list">
	{% endif %}

	{% if $productFile.ID %} {* For empty groups *}
	<li id="productFile_list_[[productID]]_[[productFile.ProductFileGroup.ID]]_[[productFile.ID]]">
		<span class="productFile_item_title">[[productFile.title()]]</span>
	</li>
	{% endif %}

	{% set lastProductFileGroup = $productFile.ProductFileGroup.ID %}
{/foreach}
</ul>



<script type="text/javascript">
	Backend.availableLanguages = {json array=$languages};

	with(Backend.ProductFile)
	{
		Links.update	 = '[[ url("backend.productFile/update") ]]';
		Links.create	 = '[[ url("backend.productFile/create") ]]';
		Links.deleteFile   = '[[ url("backend.productFile/delete") ]]';
		Links.sort	 = '[[ url("backend.productFile/sort") ]]';
		Links.edit	 = '[[ url("backend.productFile/edit") ]]';
		Links.download	 = '[[ url("backend.productFile/download") ]]';

		Messages.areYouSureYouWantToDelete = '[[ addslashes({t _are_you_sure_you_want_to_delete}) ]]';

		with(Group)
		{
			Links.update	 = '[[ url("backend.productFileGroup/update") ]]';
			Links.create	 = '[[ url("backend.productFileGroup/create") ]]';
			Links.remove   = '[[ url("backend.productFileGroup/delete") ]]';
			Links.sort	 = '[[ url("backend.productFileGroup/sort") ]]?target=productFileGroup_list_[[productID]]';
			Links.edit	 = '[[ url("backend.productFileGroup/edit") ]]';

			Messages.areYouSureYouWantToDelete = '[[ addslashes({t _are_you_sure_you_want_to_delete_group}) ]]'
		}
	}
	// create empty form
	$("productFileGroup_new_[[productID]]_form").update($("productFileGroup_item_blank").innerHTML);
	$("productFile_new_[[productID]]_form").update($("productFile_item_blank").innerHTML);
	var emptyModel = new Backend.ProductFile.Model({Product: {ID: [[productID]]}}, Backend.availableLanguages);
	var emptyController = new Backend.ProductFile.Controller($("productFile_new_[[productID]]_form").down('.productFile_form'), emptyModel);
	var emptyGroupModel = new Backend.ProductFile.Group.Model({Product: {ID: [[productID]]}}, Backend.availableLanguages);
	new Backend.ProductFile.Group.Controller($("productFileGroup_new_[[productID]]_form").down('.productFileGroup_form'), emptyGroupModel);

	Event.observe($("productFileGroup_new_[[productID]]_show"), "click", function(e)
	{
		var newForm = Backend.ProductFile.Group.Controller.prototype.getInstance($("productFileGroup_new_[[productID]]_form").down('.productFileGroup_form')).showNewForm();
		e.preventDefault();
	});

	Event.observe($("productFile_new_[[productID]]_show"), 'click', function(e) {
		e.preventDefault();
		var newForm = Backend.ProductFile.Controller.prototype.getInstance($("productFile_new_[[productID]]_form").down('.productFile_form')).showNewForm();
	});


	var groupList = ActiveList.prototype.getInstance('productFileGroup_list_[[productID]]', Backend.ProductFile.Group.Callbacks);
	ActiveList.prototype.getInstance("productFile_list_[[productID]]_", Backend.ProductFile.Callbacks);
	{assign var="lastFileGroup" value="-1"}
	{foreach item="file" from=$productFilesWithGroups}
		{% if $file.ProductFileGroup && $lastFileGroup != $file.ProductFileGroup.ID %}
			 ActiveList.prototype.getInstance('productFile_list_[[productID]]_[[file.ProductFileGroup.ID]]', Backend.ProductFile.Callbacks);
		{% endif %}
		{% set lastFileGroup = $file.ProductFileGroup.ID %}
	{/foreach}


	groupList.createSortable(true);

</script>
