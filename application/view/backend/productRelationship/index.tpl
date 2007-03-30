<script type="text/javascript" src="/public/javascript/library/tinymce/tiny_mce.js"></script>

<script type="text/javascript" src="/public/firebug/firebug.js"></script>
<script type="text/javascript" src="/public/javascript/library/prototype/prototype.js"></script>
<script type="text/javascript" src="/public/javascript/library/scriptaculous/scriptaculous.js"></script>

<script type="text/javascript" src="/public/javascript/backend/Backend.js"></script>

<script type="text/javascript" src="/public/javascript/library/livecart.js"></script>
<script type="text/javascript" src="/public/javascript/library/KeyboardEvent.js"></script>
<script type="text/javascript" src="/public/javascript/library/ActiveGrid.js"></script>
<script type="text/javascript" src="/public/javascript/library/ActiveList.js"></script>
<script type="text/javascript" src="/public/javascript/library/form/ActiveForm.js"></script>
<script type="text/javascript" src="/public/javascript/library/form/State.js"></script>
<script type="text/javascript" src="/public/javascript/library/form/Validator.js"></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlxtree/dhtmlXCommon.js"></script>
<script type="text/javascript" src="/public/javascript/library/dhtmlxtree/dhtmlXTree.js"></script>
<script type="text/javascript" src="/public/javascript/library/SectionExpander.js"></script>
<script type="text/javascript" src="/public/javascript/library/TabControl.js"></script>

<script type="text/javascript" src="/public/javascript/library/rico/ricobase.js"></script>
<script type="text/javascript" src="/public/javascript/library/rico/ricoLiveGrid.js"></script>

<script type="text/javascript" src="/public/javascript/backend/Category.js"></script>
<script type="text/javascript" src="/public/javascript/backend/SpecField.js"></script>
<script type="text/javascript" src="/public/javascript/backend/Filter.js"></script>
<script type="text/javascript" src="/public/javascript/backend/ObjectImage.js"></script>
<script type="text/javascript" src="/public/javascript/backend/Product.js"></script>
<script type="text/javascript" src="/public/javascript/backend/RelatedProduct.js"></script>

<link href="/public/stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/ActiveList.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/ActiveGrid.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/Category.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/Product.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/SpecField.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/Filter.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/CategoryImage.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/TabControl.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/dhtmlxtree/dhtmlXTree.css" media="screen" rel="Stylesheet" type="text/css"/>


<div id="productRelationshipMsg_{$productID}" style="display: none;"></div>

<fieldset class="container">
	<ul class="menu" id="specField_menu_{$categoryID}">
	    <li><a href="#selectProduct" id="selectProduct_{$productID}">{t _select_product}</a></li>
	    <li><a href="#cancelSelectProduct" id="selectProduct_{$productID}_cancel" class="hidden">{t _cancel_adding_new_related_product}</a></li>
	    <li><a href="#new" id="relatedProduct_group_new_{$categoryID}_show">{t _add_new_group}</a></li>
	    <li><a href="#new" id="relatedProduct_group_new_{$categoryID}_cancel" class="hidden">{t _cancel_adding_new_group}</a></li>
	</ul>
</fieldset>

<fieldset id="relatedProduct_group_new_{$categoryID}_form">
    {include file="backend/productRelationshipGroup/form.tpl"}
    <script type="text/javascript">
    {literal}
        with(Backend.RelatedProduct.Group)
        {
            Links.save = '{/literal}{link controller=backend.productRelationshipGroup action=save}{literal}';
            Links.remove = '{/literal}{link controller=backend.productRelationshipGroup action=delete}{literal}';
            Links.sort = '{/literal}{link controller=backend.productRelationshipGroup action=sort}{literal}';
        }
        
        var emptyGroupModel = new Backend.RelatedProduct.Group.Model({Product: {ID: {/literal}{$productID}{literal}}}, {/literal}{json array=$languages}{literal});
        new Backend.RelatedProduct.Group.Controller($("relatedProduct_group_new_{/literal}{$categoryID}{literal}_form").down('form'), emptyGroupModel);
    {/literal}
    </script>
</fieldset>

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
     /*
    try
    {
        with(Backend.RelatedProduct)
        {
            links = {};
            links.related = '{/literal}{link controller=backend.productRelationship action=addRelated}/{$productID}{literal}';
            links.deleteRelated = '{/literal}{link controller=backend.productRelationship action=delete}/{$productID}{literal}';
            links.selectProduct = '{/literal}{link controller=backend.productRelationship action=selectProduct}#cat_{$categoryID}#tabProducts__{literal}';
            links.sort = '{/literal}{link controller=backend.productRelationship action=sort}/{$productID}?target=productRelationships_{$productID}{literal}';
            
            messages = {};
            messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
            messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_relation|addslashes}{literal}';
        }
        
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
    */
</script>
{/literal}