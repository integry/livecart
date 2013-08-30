<script type="text/javascript">
{literal}
	with(Backend.RelatedProduct.Group)
	{
		Links.sort = '{/literal}{link controller="backend.productRelationshipGroup" action=sort}?target=productRelationshipGroup_list_[[productID]]{literal}';
		Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_group|addslashes}{literal}'
	}

	Backend.RelatedProduct.links = {};
	Backend.RelatedProduct.messages = {};
	with(Backend.RelatedProduct)
	{
		links.selectProduct = '{/literal}{link controller="backend.productRelationship" action=selectProduct}#cat_[[categoryID]]#tabProducts__{literal}';
		messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
		messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_relation|addslashes}{literal}';
	}
{/literal}
</script>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu">
		<li class="addProduct"><a href="#selectProduct">{t _select_product}</a></li>
		<li class="addGroup"><a href="#new">{t _add_new_group}</a></li>
		<li class="done addCancel" style="display: none;"><a href="#cancel">{t _cancel_adding_new_group}</a></li>
	</ul>
</fieldset>

<div class="newForm">
	[[ partial('backend/productRelationshipGroup/form.tpl', ['type': type]) ]]
</div>

{* No group *}
<ul id="noGroup_[[type]]_[[productID]]" class="noGroup subList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_accept_subList">
{foreach item="relationship" from=$relationshipsWithGroups}
	{% if $relationship.ProductRelationshipGroup.ID %}{break}{% endif %}
	{% if $relationship.RelatedProduct.ID %}
		<li id="[[relationship.RelatedProduct.ID]]">
			[[ partial('backend/productRelationship/addRelated.tpl', ['product': relationship.RelatedProduct]) ]]
		</li>
	{% endif %}
{/foreach}
</ul>

<ul class="activeListGroup {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit groupList">
{foreach item="relationship" from=$relationshipsWithGroups}
	{% if !$relationship.ProductRelationshipGroup.ID %}{continue}{% endif %}

	{% if $lastProductRelationshipGroup != $relationship.ProductRelationshipGroup.ID  %}
		{% if $lastProductRelationshipGroup > 0 %}</ul></li>{% endif %}
		<li id="[[relationship.ProductRelationshipGroup.ID]]" class="groupContainer">
			<span class="groupTitle">[[relationship.ProductRelationshipGroup.name]]</span>
			[[ partial("backend/productRelationshipGroup/form.tpl") ]]
			<ul id="[[relationship.ProductRelationshipGroup.ID]]" class="subList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_accept_subList">
	{% endif %}

	{% if $relationship.RelatedProduct.ID %} {* For empty groups *}
		<li id="[[relationship.RelatedProduct.ID]]">
			[[ partial('backend/productRelationship/addRelated.tpl', ['product': relationship.RelatedProduct]) ]]
		</li>
	{% endif %}

	{% set lastProductRelationshipGroup = $relationship.ProductRelationshipGroup.ID %}
{/foreach}
</ul>

<div class="blankItem" class="dom_template">[[ partial("backend/productRelationshipGroup/form.tpl") ]]</div>

<script type="text/javascript">
	Backend.RelatedProduct.Group.Controller.prototype.index([[productID]], [[type]]);
</script>