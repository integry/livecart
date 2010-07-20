<script type="text/javascript">
{literal}
	Backend.ProductBundle.links = {};
	Backend.ProductBundle.messages = {};
	with(Backend.ProductBundle)
	{
		links.selectProduct = '{/literal}{link controller=backend.productRelationship action=selectProduct}#cat_{$categoryID}#tabProducts__{literal}';
		messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
		messages.areYouSureYouWantToDelete = '{/literal}{t _confirm_bundle_delete|addslashes}{literal}';
	}
{/literal}
</script>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu">
		<li class="addProduct"><a href="#selectProduct">{t _select_product}</a></li>
	</ul>
</fieldset>

<div class="newForm">
	{include file="backend/productRelationshipGroup/form.tpl"}
</div>

<div class="total">
	{t _bundle_total_price}: <span class="price">{$total}</span>
</div>

{* No group *}
<ul id="productBundle_{$ownerID}" class="noGroup subList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_accept_subList">
{foreach item="relationship" from=$items}
	{if $relationship.RelatedProduct.ID}
		<li id="{$relationship.RelatedProduct.ID}">
			{include file="backend/productRelationship/addRelated.tpl" product=$relationship.RelatedProduct template="backend/productBundle/bundleCount.tpl"}
		</li>
	{/if}
{/foreach}
</ul>

<script type="text/javascript">
	Backend.ProductBundle.Group.Controller.prototype.index({$ownerID});
</script>

{block TRANSLATIONS}