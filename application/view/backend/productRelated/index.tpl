<a href="#selectProduct" id="selectProduct_{$productID}">{t _select_product}</a>
<ul id="relatedProducts_{$productID}" class="activeList_add_sort activeList_add_delete"></ul>

{literal}
<script type="text/javascript">
    try
    {
        Backend.RelatedProduct.links = {};
        Backend.RelatedProduct.links.related = '{/literal}{link controller=backend.productRelated action=related}{literal}';
        Backend.RelatedProduct.links.selectProduct = '{/literal}{link controller=backend.productRelated action=selectProduct}{literal}';
        
        Backend.RelatedProduct.messages = {};
        Backend.RelatedProduct.messages.selectProductTitle = '{t _select_product|add_slashes}';
        
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
        
        ActiveList.prototype.getInstance($("relatedProducts_{/literal}{$productID}{literal}"), 
        {
            beforeDelete: function(li){ console.info('beforeDelete') },
            beforeDelete: function(li, response){ console.info('afterDelete') },
            beforeDelete: function(li){ console.info('beforeSort') },
            beforeDelete: function(li, response){ console.info('afterSort') }
        });     
    }
    catch(e)
    {
        console.imfo(e);
    }
</script>
{/literal}