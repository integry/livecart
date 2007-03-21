<div id="productRelationshipMsg_{$productID}" style="display: none;"></div>

<a href="#selectProduct" id="selectProduct_{$productID}">{t _select_product}</a>
<ul id="productRelationships_{$productID}" class="activeList_add_sort activeList_add_delete">
    {foreach item="relation" from=$relationships}
        <li id="productRelationships_{$productID}_{$relation.RelatedProduct.ID}">
            {assign var="product" value=$relation.RelatedProduct}
            {include file="backend/productRelationship/addRelated.tpl" product=$product}
        </li>
    {/foreach}
</ul>

{literal}
<script type="text/javascript">
    try
    {
        Backend.RelatedProduct.links = {};
        Backend.RelatedProduct.links.related = '{/literal}{link controller=backend.productRelationship action=addRelated}/{$productID}{literal}';
        Backend.RelatedProduct.links.deleteRelated = '{/literal}{link controller=backend.productRelationship action=delete}/{$productID}{literal}';
        Backend.RelatedProduct.links.selectProduct = '{/literal}{link controller=backend.productRelationship action=selectProduct}#cat_{$categoryID}#tabProducts__{literal}';
        Backend.RelatedProduct.links.sort = '{/literal}{link controller=backend.productRelationship action=sort}/{$productID}?target=productRelationships_{$productID}{literal}';
        
        Backend.RelatedProduct.messages = {};
        Backend.RelatedProduct.messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
        Backend.RelatedProduct.messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_relation|addslashes}{literal}';
        
        Event.observe($("selectProduct_{/literal}{$productID}{literal}"), 'click', function(e) {
            Event.stop(e);
            new Backend.RelatedProduct.SelectProductPopup(
                Backend.RelatedProduct.links.selectProduct, 
                Backend.RelatedProduct.messages.selectProductTitle, 
                {
                    onProductSelect: function() { Backend.RelatedProduct.addProductToList({/literal}{$productID}{literal}, this.productID) }
                }
            );
        });
        
        ActiveList.prototype.getInstance($("productRelationships_{/literal}{$productID}{literal}"), Backend.RelatedProduct.activeListCallbacks);     
    }
    catch(e)
    {
        console.imfo(e);
    }
</script>
{/literal}