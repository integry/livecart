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
<script type="text/javascript" src="/public/javascript/backend/ProductFile.js"></script>

<link href="/public/stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/ActiveList.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/ActiveGrid.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/Category.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/Product.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/SpecField.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/ProductFile.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/Filter.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/backend/CategoryImage.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/TabControl.css" media="screen" rel="Stylesheet" type="text/css"/>
<link href="/public/stylesheet/library/dhtmlxtree/dhtmlXTree.css" media="screen" rel="Stylesheet" type="text/css"/>

{literal}
<script type="text/javascript">
    Backend.availableLanguages = {/literal}{json array=$languages}{literal};

    with(Backend.ProductFile.Group)
    {
        Links.save = '{/literal}{link controller=backend.productFileGroup action=save}{literal}';
        Links.remove = '{/literal}{link controller=backend.productFileGroup action=delete}{literal}';
        Links.sort = '{/literal}{link controller=backend.productFileGroup action=sort}?target=productFileGroup_list_{$productID}{literal}';
        Links.edit = '{/literal}{link controller=backend.productFileGroup action=edit}{literal}';
        
        Messages.areYouSureYouWantToDelete = '{/literal}{t _Are_you_sure_you_want_to_delete}{literal}'
    }
    
    
    Backend.ProductFile.links = {};
    Backend.ProductFile.messages = {};
    with(Backend.ProductFile)
    {
        links.related = '{/literal}{link controller=backend.productFile action=addRelated}/{$productID}{literal}';
        links.deleteRelated = '{/literal}{link controller=backend.productFile action=delete}/{$productID}{literal}';
        links.selectProduct = '{/literal}{link controller=backend.productFile action=selectProduct}#cat_{$categoryID}#tabProducts__{literal}';
        links.sort = '{/literal}{link controller=backend.productFile action=sort}/{$productID}{literal}';
        
        messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
        messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_this_relation|addslashes}{literal}';
    }
{/literal}
</script>
    
<div id="productFileMsg_{$productID}" style="display: none;"></div>

<fieldset class="container">
	<ul class="menu" id="productFile_menu_{$productID}">
	    <li><a href="#selectProduct" id="selectProduct_{$productID}">{t _select_product}</a></li>
	    <li><a href="#cancelSelectProduct" id="selectProduct_{$productID}_cancel" class="hidden">{t _cancel_adding_new_related_product}</a></li>
	    <li><a href="#new" id="productFileGroup_new_{$productID}_show">{t _add_new_group}</a></li>
	    <li><a href="#new" id="productFileGroup_new_{$productID}_cancel" class="hidden">{t _cancel_adding_new_group}</a></li>
	</ul>
</fieldset>

<div id="productFileGroup_new_{$productID}_form">
    {include file="backend/productFileGroup/form.tpl"}
    
    <script type="text/javascript">
    {literal}
        var emptyGroupModel = new Backend.ProductFile.Group.Model({Product: {ID: {/literal}{$productID}{literal}}}, {/literal}{json array=$languages}{literal});
        new Backend.ProductFile.Group.Controller($("productFileGroup_new_{/literal}{$productID}{literal}_form").down('.productFileGroup_form'), emptyGroupModel);
    {/literal}
    </script>
</div>


{* No group *}
<ul id="productFile_list_{$productID}_" class="productFile_list activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_productFile_list">
{foreach item="relationship" from=$relationshipsWithGroups}
    {if $relationship.productFileGroup.ID}{php}break;{/php}{/if}
    {if $relationship.RelatedProduct.ID} 
        <li id="productFile_list_{$productID}_{$relationship.productFileGroup.ID}_{$relationship.RelatedProduct.ID}">
            {include file="backend/productFile/addRelated.tpl" product=$relationship.RelatedProduct}
        </li>
    {/if}
{/foreach}
</ul>


<ul id="productFileGroup_list_{$productID}" class="activeList_add_sort activeList_add_delete activeList_add_edit productFileGroup_list">
{foreach item="relationship" from=$relationshipsWithGroups}
    {if !$relationship.productFileGroup.ID}{php}continue;{/php}{/if}
    
    {if $lastproductFileGroup != $relationship.productFileGroup.ID }
        {if $lastproductFileGroup > 0}</ul></li>{/if}
        <li id="productFileGroup_list_{$productID}_{$relationship.productFileGroup.ID}" class="productFileGroup_item">
            <span class="productFileGroup_title">{$relationship.productFileGroup.name_lang}</span>
            {include file="backend/productFileGroup/form.tpl"}	
            <ul id="productFile_list_{$productID}_{$relationship.productFileGroup.ID}" class="productFile_list activeList_add_sort activeList_add_delete activeList_accept_productFile_list">
    {/if}
    
    {if $relationship.RelatedProduct.ID} {* For empty groups *}
    <li id="productFile_list_{$productID}_{$relationship.productFileGroup.ID}_{$relationship.RelatedProduct.ID}">
    	{include file="backend/productFile/addRelated.tpl" product=$relationship.RelatedProduct}
    </li>
    {/if}

    {assign var="lastproductFileGroup" value=$relationship.productFileGroup.ID}
{/foreach}
</ul>

{literal}
<script type="text/javascript">
    try
    {
        Event.observe($("productFileGroup_new_{/literal}{$productID}{literal}_show"), "click", function(e) 
        {
            Event.stop(e);
            var newForm = Backend.ProductFile.Group.Controller.prototype.getInstance($("productFileGroup_new_{/literal}{$productID}{literal}_form").down('.productFileGroup_form')).showNewForm();
        });
        
        Event.observe($("selectProduct_{/literal}{$productID}{literal}"), 'click', function(e) {
            Event.stop(e);
            new Backend.ProductFile.SelectProductPopup(
                Backend.ProductFile.links.selectProduct, 
                Backend.ProductFile.messages.selectProductTitle, 
                {
                    onProductSelect: function() { Backend.ProductFile.addProductToList({/literal}{$productID}{literal}, this.productID) }
                }
            );
          
            Backend.ProductFile.Group.Controller.prototype.getInstance($("productFileGroup_new_{/literal}{$productID}{literal}_form").down('form')).hideNewForm();
        });
        

        {/literal}    
        var groupList = ActiveList.prototype.getInstance('productFileGroup_list_{$productID}', Backend.ProductFile.Group.Callbacks);  
        ActiveList.prototype.getInstance("productFile_list_{$productID}_", Backend.ProductFile.activeListCallbacks);
        
        {assign var="lastRelationshipGroup" value="-1"}
        {foreach item="relationship" from=$relationshipsWithGroups}
            {if $relationship.productFileGroup && $lastRelationshipGroup != $relationship.productFileGroup.ID}
                 ActiveList.prototype.getInstance('productFile_list_{$productID}_{$relationship.productFileGroup.ID}', Backend.ProductFile.activeListCallbacks);
            {/if}
            {assign var="lastRelationshipGroup" value=$relationship.productFileGroup.ID}
        {/foreach}
        {literal}
        
        groupList.createSortable();
    }
    catch(e)
    {
        console.info(e);
    }
</script>
{/literal}