<script type="text/javascript">
{literal}
	with(Backend.ProductList.Group)
	{
		Links.sort = '{/literal}{link controller=backend.productList action=sort}?target=productRelationshipGroup_list_{$productID}{literal}';
		Messages.areYouSureYouWantToDelete = '{/literal}{t _really_remove_product_list|addslashes}{literal}'
	}

	Backend.ProductList.links = {};
	Backend.ProductList.messages = {};
	with(Backend.ProductList)
	{
		links.selectProduct = '{/literal}{link controller=backend.productRelationship action=selectProduct}#cat_{$ownerID}#tabProducts__{literal}';
		messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
		messages.areYouSureYouWantToDelete = '{/literal}{t _really_remove_product_from_list|addslashes}{literal}';
	}
{/literal}
</script>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu">
		<li class="addGroup"><a href="#new">{t _add_new_list}</a></li>
		<li class="done addCancel" style="display: none;"><a href="#cancel">{t _cancel_adding_new_list}</a></li>
	</ul>
</fieldset>

<div class="newForm">
	{include file="backend/productList/form.tpl"}
</div>

<ul class="activeListGroup {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit groupList">
{foreach $items as $item}
	{if $lastProductRelationshipGroup != $item.ProductList.ID }
		{if $lastProductRelationshipGroup > 0}</ul></li>{/if}
		<li id="{$item.ProductList.ID}" class="groupContainer">
			<span class="groupTitle">{$item.ProductList.name}</span>
			{include file="backend/productList/form.tpl"}
			<ul id="{$item.ProductList.ID}" class="subList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_accept_subList">
	{/if}

	{if $item.Product.ID} {* For empty groups *}
		<li id="{$item.ID}">
			{include file="backend/productListItem/add.tpl" product=$item.Product}
		</li>
	{/if}

	{assign var="lastProductRelationshipGroup" value=$item.ProductList.ID}
{/foreach}
</ul>

<div class="blankItem" class="dom_template">
	{include file="backend/productList/form.tpl"}
</div>

<div class="addProductToListMenu dom_template">
	<ul class="menu">
		<li class="addProduct"><a href="#selectProduct">{t _add_product}</a></li>
	</ul>
</div>

{block TRANSLATIONS}

<script type="text/javascript">
	Backend.ProductList.Group.Controller.prototype.index({$ownerID});
</script>